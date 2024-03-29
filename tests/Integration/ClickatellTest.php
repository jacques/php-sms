<?php

declare(strict_types=1);
/**
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2020-2022 Jacques Marneweck.  All rights strictly reserved.
 */

namespace Jacques\SMS\Tests\Integration;

use Jacques\SMS\Clickatell;
use PHPUnit\Framework\TestCase;

class ClickatellTest extends TestCase
{
    /**
     * @vcr clickatell/auth
     */
    public function testAuth(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
    }

    /**
     * @vcr clickatell/auth__fails
     */
    public function testAuthFails(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertFalse($clickatell->auth());
    }

    /**
     * @vcr clickatell/getbalance
     */
    public function testBalance(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertEquals('12.000', $clickatell->balance());
    }

    /**
     * @vcr clickatell/getbalance__noauth
     */
    public function testBalanceNoAuth(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertEquals('12.000', $clickatell->balance());
    }

    /**
     * @vcr clickatell/getbalance__noauth__invalid_credentials
     */
    public function testBalanceNoAuthInvalidCredentials(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        $clickatell->balance();
    }

    /**
     * @vcr clickatell/getbalance__setsessionid__noauth
     */
    public function testBalanceValidSession(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertNull($clickatell->setSessionId('abcdef1234567890abcdef1234567890'));
        self::assertEquals('12.000', $clickatell->balance());
    }

    /**
     * @vcr clickatell/getbalance__fails
     */
    public function testBalanceFails(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        $clickatell->balance();
    }

    /**
     * @vcr clickatell/ping
     */
    public function testPing(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertTrue($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__fails
     */
    public function testPingFails(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertFalse($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__setsessionid__noauth
     */
    public function testPingValidSession(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertNull($clickatell->setSessionId('abcdef1234567890abcdef1234567890'));
        self::assertTrue($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__setsessionid__noauth__fails__stale_session
     */
    public function testPingStaleSession(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertNull($clickatell->setSessionId('abcdef1234567890abcdef1234567890'));
        self::assertFalse($clickatell->ping());
    }

    /**
     * @vcr clickatell/querymsg__invalid_apimsgid
     */
    public function testQueryMessageInvalidApiMsgId(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertEquals([true, 1], $clickatell->query_message('abcdef1234567890abcdef1234567890'));
    }

    /**
     * @vcr clickatell/routecoverage
     */
    public function testRouteCoverage(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertTrue($clickatell->auth());
        self::assertEquals([true, 1], $clickatell->routecoverage('27725671567'));
    }

    /**
     * @vcr clickatell/routecoverage__notroutable
     */
    public function testRouteCoverageNotRoutable(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('This prefix is not currently supported. Messages sent to this prefix will fail. Please contact support for assistance.');

        self::assertTrue($clickatell->auth());
        $clickatell->routecoverage('27801234567');
    }

    /**
     * @vcr clickatell/routecoverage__fails
     */
    public function testRouteCoverageFails(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        self::assertEquals([false, false], $clickatell->routecoverage('27725671567'));
    }

    /**
     * @vcr clickatell/sendmsg__fails
     */
    public function testSendMessageFails(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        self::assertEquals([false, false], $clickatell->send_message(['to' => '27801234567', 'message' => 'testing']));
    }

    /**
     * @vcr clickatell/sendmsg__fails__007
     */
    public function testSendMessageFails007(): void
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 007, IP Lockdown violation');

        self::assertEquals([false, false], $clickatell->send_message(['to' => '27801234567', 'message' => 'testing']));
    }
}
