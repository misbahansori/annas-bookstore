<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HandleTest extends TestCase
{
    /** @test */
    public function it_converts_an_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new \Exception('Test exception');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Exception',
                    'details' => 'Test exception'
                ]
            ]
        ])->assertStatus(500);
    }

    /** @test */
    public function it_converts_an_http_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new HttpException(404, 'Not found');

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Http Exception',
                    'details' => 'Not found'
                ]
            ]
        ])->assertStatus(404);
    }

    
    /** @test */
    public function it_converts_an_unauthenticated_exception_into_a_json_api_spec_error_response()
    {
        $handler = app(Handler::class);

        $request = Request::create('/test', 'GET');
        $request->headers->set('accept', 'application/vnd.api+json');

        $exception = new AuthenticationException();

        $response = $handler->render($request, $exception);

        TestResponse::fromBaseResponse($response)->assertJson([
            'errors' => [
                [
                    'title' => 'Unauthenticated',
                    'details' => 'You are not authenticated'
                ]
            ]
        ])->assertStatus(403);
    }
}
