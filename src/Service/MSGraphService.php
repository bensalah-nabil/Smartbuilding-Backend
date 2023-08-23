<?php

namespace App\Service;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MSGraphService
{

    private Graph $graph;
    public function __construct()
    {
        $this->graph = new Graph();
    }

    public function getUserProfile($token): ?User
    {
        try {
            $this->graph->setAccessToken($token);
            $user = $this->graph->createRequest('GET', '/me')->setReturnType(User::class)->execute();
            if (!$user) {
                throw new \RuntimeException('User not found');
            }
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getResourceById($resources, $resourceId):?array{
        foreach ($resources as $resource) {
            if ($resource['resourceId'] === $resourceId) {
                return $resource;
            }
        }
        return null;
    }
    public function getRoleById($roles, $roleId):?array{
        foreach ($roles as $role) {
            if ($role['id'] === $roleId) {
                return $role;
            }
        }
        return null;
    }
    public function getRole($token): ?array {
        $this->graph->setAccessToken($token);
        try {
            $uuid = ($this->graph->createRequest('GET', '/me')->setReturnType(User::class)->execute())->getId();
        } catch (GuzzleException|GraphException $e) { }
        try {
            $resourceArray = $this->graph
                ->createRequest('GET', '/users/' . $uuid . '/appRoleAssignments')
                ->execute()
                ->getBody()['value'];
        } catch (GuzzleException|GraphException $e) { }
        $resource = $this->getResourceById($resourceArray,$_ENV['RESOURCEID'] );
        try {
            $roleArray = $this->graph
                ->createRequest('GET', '/servicePrincipals/' . $resource['resourceId'] . '/appRoles/')
                ->execute()
                ->getBody()['value'];
        } catch (GuzzleException|GraphException $e) { }

        return ( $this->getRoleById($roleArray, $resource['appRoleId'])?? ["ROLE_USER"] );

    }
    public function getAccessToken(): string
    {
        $url = $_ENV['MICROSOFTLOGINAPI'] . $_ENV['TENANTID'] . '/oauth2/v2.0/token';
        $client = HttpClient::create();
        try {
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => [
                    'client_id' => $_ENV['CLIENTID'],
                    'client_secret' => $_ENV['CLIENTSECRET'],
                    'scope' => $_ENV['GRAPHAPI'] . '.default',
                    'grant_type' => 'client_credentials',
                ],
            ])->toArray();
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
        }

        return $response['access_token'] ?? $response['error'];
    }

    public function getRoleIdByValue(string $roleValue): ?string
    {
        $accessToken = $this->getAccessToken();
        try {
            try {
                $response = $this->graph
                    ->createRequest('GET', '/servicePrincipals/' . $_ENV['RESOURCEID'] . '/appRoles')
                    ->addHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ])
                    ->execute();
            } catch (GuzzleException|GraphException $e) {
            }

            if ($response->getStatus() === 200) {
                foreach ($response->getBody()['value'] as $role) {
                    if ($role['value'] === $roleValue) {
                        return $role['id'];
                    }
                }
            }
        } catch (TransportExceptionInterface $e) {
            // Handle the exception if needed
        }

        return null;
    }

    public function getUserRoleId($uuid):string
    {
        $accessToken = $this->getAccessToken();
        try {
            $response = $this->graph
                ->createRequest('GET', '/users/' . $uuid . '/appRoleAssignments')
                ->addHeaders(array(
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ))
                ->execute();
        } catch (GuzzleException|GraphException $e) {
        }
        $data = $response->getBody();
        if ( $response->getStatus() === 200 ) {
            return $data['value'][0]['id'];
        } else {
            return $data['error']['message'];
        }
    }
    public function deleteUserRole($uuid):int
    {
        $accessToken = $this->getAccessToken();
        try {
            $response = $this->graph
                ->createRequest('DELETE', '/users/' . $uuid . '/appRoleAssignments/' . $this->getUserRoleId($uuid))
                ->addHeaders(array(
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ))
                ->execute();
        } catch (GuzzleException|GraphException $e) {
        }
        return $response->getStatus();
    }

    public function addUserRole($uuid,$role):string | array
    {
        $accessToken = $this->getAccessToken();
        try {
            $response = $this->graph
                ->createRequest('POST', '/users/' . $uuid . '/appRoleAssignments/')
                ->addHeaders(array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ))
                ->attachBody(json_encode([
                    'principalId' => $uuid,
                    'resourceId' => $_ENV['RESOURCEID'],
                    'appRoleId' => $this->getRoleIdByValue($role),
                ]))
                ->execute();
        } catch (GuzzleException|GraphException $e) {
        }
        if ($response->getStatus() === 201 ) {
            return $response->getBody();
        } else {
            return ('error');
        }
    }
}
