<?php

declare(strict_types=1);
/**
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2020 Jacques Marneweck.  All rights strictly reserved.
 */

namespace Jacques\SMS\Tests\Unit;

use Jacques\SMS\Clickatell;
use PHPUnit\Framework\TestCase;

class ClickatellTest extends TestCase
{
    public function testConstructorMissingAllRequiredParameters(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Please pass in your Clickatell username.');

        $clickatell = new Clickatell([]);
    }

    public function testConstructorMissingUsernameParameter(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Please pass in your Clickatell username.');

        $clickatell = new Clickatell(['password' => 'Sw0rdf1sh', 'api_id' => 1_234_567]);
    }

    public function testConstructorMissingPasswordParameter(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Please pass in your Clickatell password.');

        $clickatell = new Clickatell(['username' => 'username', 'api_id' => 1_234_567]);
    }

    public function testConstructorMissingApiIdParameter(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Please pass in your Clickatell api_id.');

        $clickatell = new Clickatell(['username' => 'username', 'password' => 'Sw0rdf1sh']);
    }

    public function testSetDeliveryAckTrue(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'Sw0rdf1sh', 'api_id' => 1_234_567]);
        self::assertNull($clickatell->setDeliveryAck(true));
    }

    public function testSetSessionId(): void
    {
        $clickatell = new Clickatell(['username' => 'username', 'password' => 'Sw0rdf1sh', 'api_id' => 1_234_567]);
        self::assertNull($clickatell->setSessionId('ABC123'));
    }

    public function testSendMessageMissingToParameter(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage("Required parameter 'to' is not set.");

        $clickatell = new Clickatell(['username' => 'username', 'password' => 'Sw0rdf1sh', 'api_id' => 1_234_567]);
        $response = $clickatell->send_message(['message' => 'testing']);
    }

    public function testSendMessageMissingMessageParameter(): void
    {
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage("Required parameter 'message' is not set.");

        $clickatell = new Clickatell(['username' => 'username', 'password' => 'Sw0rdf1sh', 'api_id' => 1_234_567]);
        $response = $clickatell->send_message(['to' => '27725671567']);
    }
}
