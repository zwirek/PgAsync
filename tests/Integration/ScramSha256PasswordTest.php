<?php
declare(strict_types=1);

namespace PgAsync\Tests\Integration;

use PgAsync\Client;
use Rx\Observer\CallbackObserver;

class ScramSha256PasswordTest extends TestCase
{
    public function testScramSha256Login()
    {
        $client = new Client([
            "user" => 'scram_user',
            "database" => $this->getDbName(),
            "auto_disconnect" => true,
            "password" => "scram_password"
        ], $this->getLoop());

        $hello = null;

        $client->query("SELECT 'Hello' AS hello")
            ->subscribe(new CallbackObserver(
                function ($x) use (&$hello) {
                    $this->assertNull($hello);
                    $hello = $x['hello'];
                },
                function ($e) {
                    $this->fail('Unexpected error ' . $e);
                },
                function () {
                    $this->getLoop()->addTimer(0.1, function () {
                        $this->stopLoop();
                    });
                }
            ));

        $this->runLoopWithTimeout(2);

        $this->assertEquals('Hello', $hello);
    }
}