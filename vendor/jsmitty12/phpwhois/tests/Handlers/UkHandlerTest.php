<?php
/**
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @copyright Copyright (c) 2018 Joshua Smith
 */

namespace phpWhois\Handlers;

/**
 * UkHandlerTest
 */
class UkHandlerTest extends HandlerTest
{
    /**
     * @var \uk_handler $handler
     */
    protected $handler;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->handler            = new \uk_handler();
        $this->handler->deepWhois = false;
    }

    /**
     * @return void
     *
     * @test
     */
    public function parseVibrantDigitalFutureDotUk()
    {
        $query = 'vibrantdigitalfuture.uk';

        $fixture = $this->loadFixture($query);
        $data    = [
            "rawdata"  => $fixture,
            "regyinfo" => [],
        ];

        $actual = $this->handler->parse($data, $query);

        $expected = [
            'domain'     => [
                // 'name'    => 'vibrantdigitalfuture.uk',
                'changed' => '2017-07-28',
                'created' => '2016-07-27',
                'expires' => '2018-07-27',
            ],
            'registered' => 'yes',
        ];

        $this->assertArraySubset($expected, $actual['regrinfo'], 'Whois data may have changed');
        $this->assertArraySubset($fixture, $actual['rawdata'], 'Fixture data may be out of date');
    }
}
