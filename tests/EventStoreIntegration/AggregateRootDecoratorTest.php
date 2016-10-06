<?php
/**
 * This file is part of the prooph/event-sourcing.
 *  (c) 2014-%year% prooph software GmbH <contact@prooph.de>
 *  (c) 2015-%year% Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace ProophTest\EventSourcing\EventStoreIntegration;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use ProophTest\EventSourcing\Mock\ExtendedAggregateRootDecorator;

/**
 * Class AggregateRootDecoratorTest
 *
 * @package ProophTest\EventSourcing\EventStoreIntegration
 */
class AggregateRootDecoratorTest extends TestCase
{
    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Aggregate root class UnknownClass cannot be found
     */
    public function it_throws_exception_when_reconstitute_from_history_with_invalid_class()
    {
        $decorator = AggregateRootDecorator::newInstance();
        $decorator->fromHistory('UnknownClass', new \ArrayIterator([]));
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage The AggregateRootDecorator does not have an id
     */
    public function it_throws_exception_when_accessing_aggregate_id()
    {
        $decorator = ExtendedAggregateRootDecorator::newInstance();
        $decorator->getAggregateId();
    }
}
