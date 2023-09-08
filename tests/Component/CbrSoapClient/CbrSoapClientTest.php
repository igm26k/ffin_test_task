<?php

namespace App\Tests\Component\CbrSoapClient;

use App\Component\CbrSoapClient\CbrSoapClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CbrSoapClientTest extends KernelTestCase
{
    private CbrSoapClient $cbrSoapClient;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container           = static::getContainer();
        $this->cbrSoapClient = $container->get(CbrSoapClient::class);

    }

    public function testGetCursOnDate()
    {
        $date = (new \DateTime('2004-05-02'));

        $result = $this->cbrSoapClient->getCursOnDate($date);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertIsObject($result[0]);

        $this->assertIsString($result[0]->Vname);
        $this->assertIsNumeric($result[0]->Vnom);
        $this->assertIsNumeric($result[0]->Vcurs);
        $this->assertIsNumeric($result[0]->Vcode);
        $this->assertIsString($result[0]->VchCode);
        $this->assertIsNumeric($result[0]->VunitRate);
    }

    public function testGetLatestDateTime()
    {
        $result = $this->cbrSoapClient->getLatestDateTime();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);

        $date = new \DateTime($result);
        $this->assertInstanceOf(\DateTime::class, $date);
    }
}
