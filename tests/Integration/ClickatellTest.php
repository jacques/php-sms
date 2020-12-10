<?php declare(strict_types=1);
/**
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2020 Jacques Marneweck.  All rights strictly reserved.
 */

namespace Jacques\SMS\Tests\Integration;

use Jacques\SMS\Clickatell;
use PHPUnit\Framework\TestCase;

class ClickatellTest extends TestCase
{
    /**
     * @vcr clickatell/auth
     */
    public function testAuth()
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
    }

    /**
     * @vcr clickatell/auth__fails
     */
    public function testAuthFails()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertFalse($clickatell->auth());
    }

    /**
     * @vcr clickatell/getbalance
     */
    public function testBalance()
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertEquals('12.000', $clickatell->balance());
    }

    /**
     * @vcr clickatell/getbalance__noauth
     */
    public function testBalanceNoAuth()
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
    public function testBalanceFails()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        $clickatell->balance();
    }

    /**
     * @vcr clickatell/ping
     */
    public function testPing()
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertTrue($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__fails
     */
    public function testPingFails()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertFalse($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__setsessionid__noauth
     */
    public function testPingValidSession()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertNull($clickatell->setSessionId('abcdef1234567890abcdef1234567890'));
        self::assertTrue($clickatell->ping());
    }

    /**
     * @vcr clickatell/ping__setsessionid__noauth__fails__stale_session
     */
    public function testPingStaleSession()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertNull($clickatell->setSessionId('abcdef1234567890abcdef1234567890'));
        self::assertFalse($clickatell->ping());
    }

    /**
     * @vcr clickatell/querymsg__invalid_apimsgid
     */
    public function testQueryMessageInvalidApiMsgId()
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'sw0rdf1sh', 'api_id' => 1_234_567]);

        self::assertTrue($clickatell->auth());
        self::assertEquals([true, 1], $clickatell->query_message('abcdef1234567890abcdef1234567890'));
    }

    /**
     * @vcr clickatell/routecoverage
     */
    public function testRouteCoverage()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::assertTrue($clickatell->auth());
        self::assertEquals([true, 1], $clickatell->routecoverage('27725671567'));
    }

    /**
     * @vcr clickatell/routecoverage__notroutable
     */
    public function testRouteCoverageNotRoutable()
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
    public function testRouteCoverageFails()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        self::assertEquals([false, false], $clickatell->routecoverage('27725671567'));
    }

    /**
     * @vcr clickatell/sendmsg__fails
     */
    public function testSendMessageFails()
    {
        $clickatell = new Clickatell(['username' => 'test', 'password' => 'test', 'api_id' => 1]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('ERR: 001, Authentication failed');

        self::assertEquals([false, false], $clickatell->send_message(['to' => '27801234567', 'message' => 'testing']));
    }

}
