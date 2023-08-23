<?php

namespace App\Security;

use App\Entity\User as UserE;
use App\Repository\UserRepository;
use App\Service\MSGraphService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Microsoft\Graph\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class UserAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly MSGraphService $graph,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $manager
    ) { }
    private function validateToken(string $token): bool
    {
        return true;
    }
    private function getUserProfile(Request $request): User
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);

        // Validate and verify the token before using it
        if (!$this->validateToken($token)) {
            throw new AuthenticationException('Invalid or expired token.');
        }
        $user = $this->graph->getUserProfile($token);
        if ($user === null) {
            throw new AuthenticationException('User not found.');
        }
        return $user;
    }

    private function getId(User $user):int
    {
        return ($this->userRepository->findOneBy(['uuid' => $user->getId()]))->getId();
    }

    public function supports(Request $request): ?bool
    {
        $hasAuthorizationHeader = $request->headers->has('Authorization');
        $route = $request->attributes->get('_route');
        $excludeRoute = 'user_create';

        $user = $this->getUserProfile($request);
        $uuid = $user->getId();
        $userdb = $this->userRepository->findOneBy(['uuid' => $uuid]);
        if( $userdb == null){
            $newUser = (New UserE())
                ->setUuid($user->getId())
                ->setEmail($user->getMail())
                ->setNom($user->getGivenName())
                ->setPrenom($user->getSurname())
                ->setUuid($user->getId())
                ->setPoste($user->getJobTitle())
                ->setTelephone($user->getMobilePhone());
            $this->manager->persist($newUser);
            $this->manager->flush();
        }

        return $hasAuthorizationHeader && $route !== $excludeRoute ;
    }

    public function authenticate(Request $request): Passport
    {
        $user = $this->getUserProfile($request);
        if ($user ) {
            $userId = $this->getId($user);
            $userBadge = new UserBadge($userId, fn(string $userId) => $this->userRepository->find($userId));
            return new SelfValidatingPassport($userBadge);
        }
        throw new AuthenticationException('User not authenticated.');
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
