<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\EventListener\NotificationSubscriber;

/**
 * Test related class
 *
 */
class NotificationSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $subscriber;

    protected function setUp()
    {
        $this->subscriber = new NotificationSubscriber();
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
            ),
            NotificationSubscriber::getSubscribedEvents()
        );
    }

    public function testRegisterHandler()
    {
        $notifier = $this->getNotifierMock();
        $this->subscriber->registerNotifier($notifier);

        $this->assertEquals(array($notifier), $this->subscriber->getNotifiers());
    }

    public function testAfterJobExecution()
    {
        $mailNotifier = $this->getNotifierMock();
        $nullNotifier = $this->getNotifierMock();
        $this->subscriber->registerNotifier($mailNotifier);
        $this->subscriber->registerNotifier($nullNotifier);

        $jobExecution = $this->getJobExecutionMock();

        $mailNotifier->expects($this->once())
            ->method('notify')
            ->with($jobExecution);

        $nullNotifier->expects($this->once())
            ->method('notify')
            ->with($jobExecution);

        $event = $this->getJobExecutionEventMock($jobExecution);
        $this->subscriber->afterJobExecution($event);
    }

    private function getNotifierMock()
    {
        return $this->getMock('Akeneo\Bundle\BatchBundle\Notification\Notifier');
    }

    private function getJobExecutionEventMock($jobExecution = null)
    {
        $event = $this
            ->getMockBuilder('Akeneo\Component\Batch\Event\JobExecutionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        return $event;
    }

    private function getJobExecutionMock()
    {
        return $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
