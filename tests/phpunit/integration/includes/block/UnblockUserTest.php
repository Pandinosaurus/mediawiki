<?php

namespace MediaWiki\Tests\Block;

use MediaWiki\Block\DatabaseBlock;
use MediaWiki\Block\UnblockUserFactory;
use MediaWiki\Tests\Unit\Permissions\MockAuthorityTrait;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * @group Blocking
 * @group Database
 */
class UnblockUserTest extends MediaWikiIntegrationTestCase {
	use MockAuthorityTrait;

	private User $user;
	private UnblockUserFactory $unblockUserFactory;

	protected function setUp(): void {
		parent::setUp();

		// Prepare users
		$this->user = $this->getTestUser()->getUser();

		// Prepare factory
		$this->unblockUserFactory = $this->getServiceContainer()->getUnblockUserFactory();
	}

	/**
	 * @covers \MediaWiki\Block\UnblockUser::unblock
	 */
	public function testValidUnblock() {
		$performer = $this->mockRegisteredUltimateAuthority();
		$block = new DatabaseBlock( [
			'address' => $this->user->getName(),
			'by' => $performer->getUser()
		] );
		$this->getServiceContainer()->getDatabaseBlockStore()->insertBlock( $block );

		$this->assertInstanceOf( DatabaseBlock::class, $this->user->getBlock() );
		$status = $this->unblockUserFactory->newUnblockUser(
			$this->user,
			$performer,
			'test'
		)->unblock();
		$this->assertStatusOK( $status );
		$this->assertNotInstanceOf(
			DatabaseBlock::class,
			User::newFromName(
				$this->user->getName()
			)
			->getBlock()
		);
	}

	/**
	 * @covers \MediaWiki\Block\UnblockUser::unblockUnsafe
	 */
	public function testNotBlocked() {
		$this->user = User::newFromName( $this->user->getName() ); // Reload the user object
		$status = $this->unblockUserFactory->newUnblockUser(
			$this->user,
			$this->mockRegisteredUltimateAuthority(),
			'test'
		)->unblock();
		$this->assertStatusError( 'ipb_cant_unblock', $status );
	}
}
