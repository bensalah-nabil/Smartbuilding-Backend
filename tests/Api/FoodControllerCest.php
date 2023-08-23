<?php
namespace App\Tests\Api;

use App\Entity\Food;
use Codeception\Util\HttpCode;
use App\Tests\Support\ApiTester;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FoodControllerCest
{
    public function _before(ApiTester $I)
    {
        $token = require codecept_data_dir('token.php');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer '. $token );
    }

    public function getAllFoodsSuccess(ApiTester $I)
   {
        $I->sendGET('/foods');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    public function getFoodByIdSuccess(ApiTester $I)
    {
        $food = $I->haveInRepository(Food::class,
            [
                'name' => 'Test Food',
                'category' => 'Test Category',
                'price' => 10.0
            ]);
        $I->sendGET('/foods/' . $food);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['name' => 'Test Food']);
    }

//    public function getFoodByIdNotFound(ApiTester $I)
//    {
//        $I->sendGET('/foods/9999');
//        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
//    }

//    public function testCreateFoodSuccess(ApiTester $I)
//    {
//        $I->wantTo('Create a new food item');
//        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
//        $I->wantTo('add a new food');
//        // Food
//        $foodData = [
//            'name' => 'Pizza',
//            'category' => 'FastFood',
//            'price' => 20,
//        ];
//        // Image
//        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
//        file_put_contents($tempFile, 'Test image content'); // Add content to the temporary file
//        $imageFile = new UploadedFile($tempFile, 'test-image.jpg', 'image/jpeg', null, true);
//        // Request
//        $I->sendPOST('/foods',
//            ['food' => json_encode($foodData)],
//            ['image' => $imageFile]
//        );
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseMatchesJsonType([
//            'id' => 'integer',
//            'name' => 'string',
//            'category' => 'string',
//            'price' => 'float',
//            'image' => 'string'
//        ]);
//    }

//    public function testCreateFoodFailure(ApiTester $I)
//    {
//        $formData = [
//            'name' => '',
//            'category' => 'Main Course',
//            'price' => -10.0,
//        ];
//
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->sendPOST('/foods', [
//            'food' => json_encode($formData),
//        ]);
//
//        $I->seeResponseCodeIs(400);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson([
//            'message' => 'Invalid JSON data',
//        ]);
//    }
    public function testUpdateFoodSuccess(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $food = $I->haveInRepository(Food::class, ['name' => 'Test Food', 'category' => 'Test Category', 'price' => 10.0]);
        $imagePath = codecept_data_dir('test-image.jpg');
        $uploadedFile = new UploadedFile($imagePath, 'test_image.jpg', 'image/jpeg', null, true);
        $I->sendPOST('/foods/update', [
            'food' => json_encode(['id' => $food, 'name' => 'Updated Test Food', 'category' => 'Updated Test Category', 'price' => 10.0]),
            'image' => $uploadedFile
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['name' => 'Updated Test Food']);
    }
    public function testFoodUpdateFailure(ApiTester $I)
    {
        // Send an invalid JSON payload to update a food item
        $I->sendPOST('foods/update', [
            'food' => 'invalid-json',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'message' => 'Invalid request.'
        ]);
    }
    public function testGetAllFoodsEmpty(ApiTester $I)
    {
        // Delete all existing foods
        $I->sendGET('/foods');
        $foods = json_decode($I->grabResponse(), true);
        foreach ($foods as $food) {
            $I->sendDELETE('/foods/' . $food['id']);
        }

        // Request all foods and check for empty response
        $I->sendGET('/foods');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseEquals('[]');
    }
//    public function testGetFoodByIdNotFound(ApiTester $I)
//    {
//        $I->sendGET('/foods/9999');
//        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
//        $I->seeResponseContainsJson(['message' => 'Food not found.']);
//    }
    public function testDeleteFoodNotFound(ApiTester $I)
    {
        $I->sendDELETE('/foods/9999');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson(['message' => 'Food not found.']);
    }
    public function deleteFoodSuccess(ApiTester $I)
    {
        $food = $I->haveInRepository(Food::class,
            [
                'name' => 'Test Food',
                'category' => 'Test Category',
                'price' => 10.0
            ]);
        $I->sendDELETE('/foods/' . $food);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
//    public function testCreateFoodMissingFields(ApiTester $I)
//    {
//        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
//        $I->sendPOST('/foods',
//            ['food' => json_encode(['name' => 'Pizza'])]
//        );
//        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
//        $I->seeResponseContainsJson(['message' => 'Invalid JSON data']);
//    }
//    public function testUpdateFoodMissingFields(ApiTester $I)
//    {
//        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
//        $food = $I->haveInRepository(Food::class, ['name' => 'Test Food', 'category' => 'Test Category', 'price' => 10.0]);
//        $I->sendPOST('/foods/update', [
//            'food' => json_encode(['id' => $food, 'name' => 'Updated Test Food']),
//        ]);
//        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
//        $I->seeResponseContainsJson(['message' => 'Invalid JSON data']);
//    }
    public function testUpdateFoodNotFound(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $imagePath = codecept_data_dir('test-image.jpg');
        $uploadedFile = new UploadedFile($imagePath, 'test_image.jpg', 'image/jpeg', null, true);
        $I->sendPOST('/foods/update', [
            'food' => json_encode(['id' => 9999, 'name' => 'Updated Test Food', 'category' => 'Updated Test Category', 'price' => 10.0]),
            'image' => $uploadedFile
        ]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson(['message' => 'Food not found.']);
    }

}
