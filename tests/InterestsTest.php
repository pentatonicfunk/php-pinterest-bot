<?php

namespace seregazhuk\tests;

use seregazhuk\PinterestBot\Api\Providers\Interests;

/**
 * Class InterestsTest.
 */
class InterestsTest extends ProviderTest
{
    /**
     * @var Interests
     */
    protected $provider;
    /**
     * @var
     */
    protected $providerClass = Interests::class;

    /** @test */
    public function followInterest()
    {
        $response = $this->createSuccessApiResponse();
        $error = $this->createErrorApiResponse();

        $this->mock->shouldReceive('followMethodCall')->once()->andReturn($response);
        $this->mock->shouldReceive('followMethodCall')->once()->andReturn($error);

        $this->assertTrue($this->provider->follow(1111));
        $this->assertFalse($this->provider->follow(1111));
    }

    /** @test */
    public function unFollowInterest()
    {
        $request = $this->createSuccessApiResponse();
        $error = $this->createErrorApiResponse();

        $this->mock->shouldReceive('followMethodCall')->once()->andReturn($request);
        $this->mock->shouldReceive('followMethodCall')->once()->andReturn($error);

        $this->assertTrue($this->provider->unFollow(1111));
        $this->assertFalse($this->provider->unFollow(1111));
    }
}
