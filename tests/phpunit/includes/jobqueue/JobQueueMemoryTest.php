<?php

use MediaWiki\JobQueue\Job;
use MediaWiki\JobQueue\JobQueue;
use MediaWiki\JobQueue\JobQueueMemory;
use MediaWiki\JobQueue\JobSpecification;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\WikiMap\WikiMap;

/**
 * @covers \MediaWiki\JobQueue\JobQueueMemory
 *
 * @group JobQueue
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class JobQueueMemoryTest extends PHPUnit\Framework\TestCase {

	use MediaWikiCoversValidator;

	/**
	 * @return JobQueueMemory
	 */
	private function newJobQueue() {
		$services = MediaWikiServices::getInstance();

		return JobQueue::factory( [
			'class' => JobQueueMemory::class,
			'domain' => WikiMap::getCurrentWikiDbDomain()->getId(),
			'type' => 'null',
			'idGenerator' => $services->getGlobalIdGenerator(),
		] );
	}

	private function newJobSpecification() {
		return new JobSpecification(
			'null',
			[ 'customParameter' => null ],
			[],
			Title::makeTitle( NS_MAIN, 'Custom title' )
		);
	}

	public function testGetAllQueuedJobs() {
		$queue = $this->newJobQueue();
		$this->assertCount( 0, $queue->getAllQueuedJobs() );

		$queue->push( $this->newJobSpecification() );
		$this->assertCount( 1, $queue->getAllQueuedJobs() );
	}

	public function testGetAllAcquiredJobs() {
		$queue = $this->newJobQueue();
		$this->assertCount( 0, $queue->getAllAcquiredJobs() );

		$queue->push( $this->newJobSpecification() );
		$this->assertCount( 0, $queue->getAllAcquiredJobs() );

		$queue->pop();
		$this->assertCount( 1, $queue->getAllAcquiredJobs() );
	}

	public function testJobFromSpecInternal() {
		$queue = $this->newJobQueue();
		$job = $queue->jobFromSpecInternal( $this->newJobSpecification() );
		$this->assertInstanceOf( Job::class, $job );
		$this->assertSame( 'null', $job->getType() );
		$this->assertArrayHasKey( 'customParameter', $job->getParams() );
		$this->assertSame( 'Custom title', $job->getTitle()->getText() );
	}

}
