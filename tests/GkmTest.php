<?php

use PHPUnit\Framework\TestCase;

use Gkm\Gkm;
use Gkm\GkmClient;
use Gkm\Domain\GeoKrety;

class MockResponse {
    public function getBody() { return "body"; }
    public function getStatusCode() { return 123; }
}

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class GkmTest extends TestCase {

    const BASIC_GEOKRETY_XML = <<< EOXML
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?><gkxml version="1.0" date="2019-05-17 11:31:53"><geokrety>
  <geokret date="2014-09-03" missing="1" ownername="kumy" id="46464" dist="0" lat="43.69365" lon="6.86097" waypoint="OX5BRQK" owner_id="26422" state="0" type="0" last_pos_id="586879" last_log_id="586879" image="14097735378mgfc.png">c:geo One</geokret>
</geokrety></gkxml>
EOXML;


    public function test_gkm() {
        // GIVEN
        $gkmClientStub = $this->stubGkmClient(self::BASIC_GEOKRETY_XML);
        $expectedGeokrety = new Geokrety();
        $expectedGeokrety->id = "46464";
        $expectedGeokrety->dateMoved = "2014-09-03";
        $expectedGeokrety->ownerName = "kumy";
        $expectedGeokrety->ownerId = "26422";
        $expectedGeokrety->distanceTraveledKm = 0;
        $expectedGeokrety->waypointCode = "OX5BRQK";
        $expectedGeokrety->state = "0";
        $expectedGeokrety->typeId = "0";
        $expectedGeokrety->positionLat = "43.69365";
        $expectedGeokrety->positionLon = "6.86097";
        $expectedGeokrety->imageSrc = "14097735378mgfc.png";
        // $expectedGeokrety->imageTitle;
        $expectedGeokrety->name = "c:geo One";
        $expectedGeokrety->lastMoveId = "586879";

        $gkm = new Gkm();
        $this->setProtectedProperty($gkm, "client", $gkmClientStub);

        // WHEN
        $rez = $gkm->getGeokretyById(1334);

        // THEN
        $this->assertEquals($expectedGeokrety, $rez);
    }

    private function stubGkmClient($xmlToReturn) {
        $geokretyResponseStub = $this->createMock(MockResponse::class);
        $geokretyResponseStub->method('getBody')->willReturn($xmlToReturn);
        $geokretyResponseStub->method('getStatusCode')->willReturn(200);

        $gkmClientStub = $this->createMock(GkmClient::class);

        $gkmClientStub->method('getBasicGeokretyById')
                     ->willReturn($geokretyResponseStub);

        return $gkmClientStub;
    }

    // src: https://stackoverflow.com/questions/18558183/phpunit-mockbuilder-set-mock-object-internal-property
    /**
     * Sets a protected property on a given object via reflection
     *
     * @param $object - instance in which protected value is being modified
     * @param $property - property on instance being modified
     * @param $value - new value of the property being modified
     *
     * @return void
     */
    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

}
