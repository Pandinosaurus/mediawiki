<?php

use MediaWiki\Api\ApiResult;
use MediaWiki\Context\DerivativeContext;
use MediaWiki\Context\RequestContext;
use MediaWiki\Linker\Linker;
use MediaWiki\Logging\LogFormatter;
use MediaWiki\Logging\LogPage;
use MediaWiki\Logging\ManualLogEntry;
use MediaWiki\MainConfigNames;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\SimpleAuthority;
use MediaWiki\RCFeed\IRCColourfulRCFeedFormatter;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentityValue;

/**
 * @group Database
 */
class LogFormatterTest extends MediaWikiLangTestCase {
	/** @var array */
	private static $oldExtMsgFiles;

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var RequestContext
	 */
	protected $context;

	/**
	 * @var Title
	 */
	protected $target;

	/**
	 * @var string
	 */
	protected $user_comment;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		global $wgExtensionMessagesFiles;
		self::$oldExtMsgFiles = $wgExtensionMessagesFiles;
		$wgExtensionMessagesFiles['LogTests'] = __DIR__ . '/LogTests.i18n.php';
	}

	public static function tearDownAfterClass(): void {
		global $wgExtensionMessagesFiles;
		$wgExtensionMessagesFiles = self::$oldExtMsgFiles;

		parent::tearDownAfterClass();
	}

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValues( [
			MainConfigNames::LogTypes => [ 'phpunit' ],
			MainConfigNames::LogActionsHandlers => [ 'phpunit/test' => LogFormatter::class,
				'phpunit/param' => LogFormatter::class ],
		] );

		$this->user = User::newFromName( 'Testuser' );
		$this->title = Title::makeTitle( NS_MAIN, 'SomeTitle' );
		$this->target = Title::makeTitle( NS_MAIN, 'TestTarget' );

		$this->context = new RequestContext();
		$this->context->setUser( $this->user );
		$this->context->setTitle( $this->title );
		$this->context->setLanguage( RequestContext::getMain()->getLanguage() );

		$this->user_comment = '<User comment about action>';
	}

	public function newLogEntry( $action, $params ) {
		$logEntry = new ManualLogEntry( 'phpunit', $action );
		$logEntry->setPerformer( $this->user );
		$logEntry->setTarget( $this->title );
		$logEntry->setComment( 'A very good reason' );

		$logEntry->setParameters( $params );

		return $logEntry;
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::setShowUserToolLinks
	 */
	public function testNormalLogParams() {
		$entry = $this->newLogEntry( 'test', [] );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$formatter->setShowUserToolLinks( false );
		$paramsWithoutTools = $formatter->getMessageParametersForTesting();

		$formatter2 = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter2->setContext( $this->context );
		$formatter2->setShowUserToolLinks( true );
		$paramsWithTools = $formatter2->getMessageParametersForTesting();

		$userLink = Linker::userLink(
			$this->user->getId(),
			$this->user->getName()
		);

		$userTools = Linker::userToolLinksRedContribs(
			$this->user->getId(),
			$this->user->getName(),
			$this->user->getEditCount(),
			false
		);

		$titleLink = Linker::link( $this->title, null, [], [] );

		// $paramsWithoutTools and $paramsWithTools should be only different
		// in index 0
		$this->assertEquals( $paramsWithoutTools[1], $paramsWithTools[1] );
		$this->assertEquals( $paramsWithoutTools[2], $paramsWithTools[2] );

		$this->assertEquals( Message::rawParam( $userLink ), $paramsWithoutTools[0] );
		$this->assertEquals( Message::rawParam( $userLink . $userTools ), $paramsWithTools[0] );

		$this->assertEquals( $this->user->getName(), $paramsWithoutTools[1] );

		$this->assertEquals( Message::rawParam( $titleLink ), $paramsWithoutTools[2] );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeRaw() {
		$params = [ '4:raw:raw' => Linker::link( $this->title, null, [], [] ) ];
		$expected = Linker::link( $this->title, null, [], [] );

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeMsg() {
		$params = [ '4:msg:msg' => 'log-description-phpunit' ];
		$expected = wfMessage( 'log-description-phpunit' )->text();

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeMsgContent() {
		$params = [ '4:msg-content:msgContent' => 'log-description-phpunit' ];
		$expected = wfMessage( 'log-description-phpunit' )->inContentLanguage()->text();

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeNumber() {
		global $wgLang;

		$params = [ '4:number:number' => 123456789 ];
		$expected = $wgLang->formatNum( 123456789 );

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeUserLink() {
		$params = [ '4:user-link:userLink' => $this->user->getName() ];
		$expected = Linker::userLink(
			$this->user->getId(),
			$this->user->getName()
		);

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeUserLink_empty() {
		$params = [ '4:user-link:userLink' => ':' ];

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );

		$this->context->setLanguage( 'qqx' );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();
		$this->assertStringContainsString( '(empty-username)', $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypeTitleLink() {
		$params = [ '4:title-link:titleLink' => $this->title->getText() ];
		$expected = Linker::link( $this->title, null, [], [] );

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getActionText
	 */
	public function testLogParamsTypePlain() {
		$params = [ '4:plain:plain' => 'Some plain text' ];
		$expected = 'Some plain text';

		$entry = $this->newLogEntry( 'param', $params );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		$logParam = $formatter->getActionText();

		$this->assertEquals( $expected, $logParam );
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getPerformerElement
	 * @dataProvider provideLogElement
	 */
	public function testGetPerformerElement( $deletedFlag, $allowedAction ) {
		$entry = $this->newLogEntry( 'param', [] );
		$entry->setPerformer( new UserIdentityValue( 1328435, 'Test' ) );
		if ( $deletedFlag !== 'none' ) {
			$entry->setDeleted(
				LogPage::DELETED_USER |
					( $deletedFlag === 'suppressed' ? LogPage::DELETED_RESTRICTED : 0 )
			);
		}

		$context = new DerivativeContext( $this->context );
		if ( $allowedAction !== 'none' ) {
			$context->setAuthority( new SimpleAuthority(
				$this->context->getUser(),
				[ $deletedFlag === 'suppressed' ? 'suppressrevision' : 'deletedhistory' ]
			) );
		}

		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $context );
		if ( $allowedAction === 'view-for-user' ) {
			$formatter->setAudience( LogFormatter::FOR_THIS_USER );
		}

		$element = $formatter->getPerformerElement();
		if ( $allowedAction === 'none' ||
			( $deletedFlag !== 'none' && $allowedAction === 'view-public' )
		) {
			$this->assertStringNotContainsString( 'User:Test', $element );
		} else {
			$this->assertStringContainsString( 'User:Test', $element );
		}

		if ( $deletedFlag === 'none' ) {
			$this->assertStringNotContainsString( 'history-deleted', $element );
		} else {
			$this->assertStringContainsString( 'history-deleted', $element );
		}
		if ( $deletedFlag === 'suppressed' ) {
			$this->assertStringContainsString( 'mw-history-suppressed', $element );
		} else {
			$this->assertStringNotContainsString( 'mw-history-suppressed', $element );
		}
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getComment
	 * @dataProvider provideLogElement
	 */
	public function testLogComment( $deletedFlag, $allowedAction ) {
		$entry = $this->newLogEntry( 'test', [] );
		if ( $deletedFlag !== 'none' ) {
			$entry->setDeleted(
				LogPage::DELETED_COMMENT |
					( $deletedFlag === 'suppressed' ? LogPage::DELETED_RESTRICTED : 0 )
			);
		}

		$context = new DerivativeContext( $this->context );
		if ( $allowedAction !== 'none' ) {
			$context->setAuthority( new SimpleAuthority(
				$this->context->getUser(),
				[ $deletedFlag === 'suppressed' ? 'suppressrevision' : 'deletedhistory' ]
			) );
		}

		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $context );
		if ( $allowedAction === 'view-for-user' ) {
			$formatter->setAudience( LogFormatter::FOR_THIS_USER );
		}

		$expectedComment = ltrim( $this->getServiceContainer()->getCommentFormatter()->formatBlock( $entry->getComment() ) );
		$comment = $formatter->getComment();

		if ( $allowedAction === 'none' ||
			( $deletedFlag !== 'none' && $allowedAction === 'view-public' )
		) {
			$this->assertStringNotContainsString( $expectedComment, $comment );
		} else {
			$this->assertStringContainsString( $expectedComment, $comment );
		}
		if ( $deletedFlag === 'none' ) {
			$this->assertStringNotContainsString( 'history-deleted', $comment );
		} else {
			$this->assertStringContainsString( 'history-deleted', $comment );
		}
		if ( $deletedFlag === 'suppressed' ) {
			$this->assertStringContainsString( 'mw-history-suppressed', $comment );
		} else {
			$this->assertStringNotContainsString( 'mw-history-suppressed', $comment );
		}
	}

	public static function provideLogElement() {
		return [
			[ 'none', 'view' ],
			[ 'deleted', 'none' ],
			[ 'deleted', 'view-for-user' ],
			[ 'deleted', 'view-public' ],
			[ 'suppressed', 'none' ],
			[ 'suppressed', 'view-for-user' ],
			[ 'suppressed', 'view-public' ],
		];
	}

	/**
	 * @dataProvider provideApiParamFormatting
	 * @covers \MediaWiki\Logging\LogFormatter::formatParametersForApi
	 * @covers \MediaWiki\Logging\LogFormatter::formatParameterValueForApi
	 */
	public function testApiParamFormatting( $key, $value, $expected ) {
		$entry = $this->newLogEntry( 'param', [ $key => $value ] );
		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $entry );
		$formatter->setContext( $this->context );

		ApiResult::setIndexedTagName( $expected, 'param' );
		ApiResult::setArrayType( $expected, 'assoc' );

		$this->assertEquals( $expected, $formatter->formatParametersForApi() );
	}

	public static function provideApiParamFormatting() {
		return [
			[ 0, 'value', [ 'value' ] ],
			[ 'named', 'value', [ 'named' => 'value' ] ],
			[ '::key', 'value', [ 'key' => 'value' ] ],
			[ '4::key', 'value', [ 'key' => 'value' ] ],
			[ '4:raw:key', 'value', [ 'key' => 'value' ] ],
			[ '4:plain:key', 'value', [ 'key' => 'value' ] ],
			[ '4:bool:key', '1', [ 'key' => true ] ],
			[ '4:bool:key', '0', [ 'key' => false ] ],
			[ '4:number:key', '123', [ 'key' => 123 ] ],
			[ '4:number:key', '123.5', [ 'key' => 123.5 ] ],
			[ '4:array:key', [], [ 'key' => [ ApiResult::META_TYPE => 'array' ] ] ],
			[ '4:assoc:key', [], [ 'key' => [ ApiResult::META_TYPE => 'assoc' ] ] ],
			[ '4:kvp:key', [], [ 'key' => [ ApiResult::META_TYPE => 'kvp' ] ] ],
			[ '4:timestamp:key', '20150102030405', [ 'key' => '2015-01-02T03:04:05Z' ] ],
			[ '4:msg:key', 'parentheses', [
				'key_key' => 'parentheses',
				'key_text' => wfMessage( 'parentheses' )->text(),
			] ],
			[ '4:msg-content:key', 'parentheses', [
				'key_key' => 'parentheses',
				'key_text' => wfMessage( 'parentheses' )->inContentLanguage()->text(),
			] ],
			[ '4:title:key', 'project:foo', [
				'key_ns' => NS_PROJECT,
				'key_title' => Title::makeTitle( NS_PROJECT, 'Foo' )->getFullText(),
			] ],
			[ '4:title-link:key', 'project:foo', [
				'key_ns' => NS_PROJECT,
				'key_title' => Title::makeTitle( NS_PROJECT, 'Foo' )->getFullText(),
			] ],
			[ '4:title-link:key', '<invalid>', [
				'key_ns' => NS_SPECIAL,
				'key_title' => SpecialPage::getTitleFor( 'Badtitle', '<invalid>' )->getFullText(),
			] ],
			[ '4:user:key', 'foo', [ 'key' => 'Foo' ] ],
			[ '4:user-link:key', 'foo', [ 'key' => 'Foo' ] ],
		];
	}

	/**
	 * The testIrcMsgForAction* tests are supposed to cover the hacky
	 * LogFormatter::getIRCActionText / T36508
	 *
	 * Third parties bots listen to those messages. They are clever enough
	 * to fetch the i18n messages from the wiki and then analyze the IRC feed
	 * to reverse engineer the $1, $2 messages.
	 * One thing bots cannot detect is when MediaWiki change the meaning of
	 * a message like what happened when we deployed 1.19. $1 became the user
	 * performing the action which broke basically all bots around.
	 *
	 * Should cover the following log actions (which are most commonly used by bots):
	 * - block/block
	 * - block/unblock
	 * - block/reblock
	 * - delete/delete
	 * - delete/restore
	 * - newusers/create
	 * - newusers/create2
	 * - newusers/autocreate
	 * - move/move
	 * - move/move_redir
	 * - protect/protect
	 * - protect/modifyprotect
	 * - protect/unprotect
	 * - protect/move_prot
	 * - upload/upload
	 * - merge/merge
	 * - import/upload
	 * - import/interwiki
	 *
	 * As well as the following Auto Edit Summaries:
	 * - blank
	 * - replace
	 * - rollback
	 * - undo
	 */

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeBlock() {
		$sep = $this->context->msg( 'colon-separator' )->text();

		# block/block
		$this->assertIRCComment(
			$this->context->msg( 'blocklogentry', 'SomeTitle', 'duration', '(flags)' )->plain()
			. $sep . $this->user_comment,
			'block', 'block',
			[
				'5::duration' => 'duration',
				'6::flags' => 'flags',
			],
			$this->user_comment
		);
		# block/block - legacy
		$this->assertIRCComment(
			$this->context->msg( 'blocklogentry', 'SomeTitle', 'duration', '(flags)' )->plain()
			. $sep . $this->user_comment,
			'block', 'block',
			[
				'duration',
				'flags',
			],
			$this->user_comment,
			'',
			true
		);
		# block/unblock
		$this->assertIRCComment(
			$this->context->msg( 'unblocklogentry', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'block', 'unblock',
			[],
			$this->user_comment
		);
		# block/reblock
		$this->assertIRCComment(
			$this->context->msg( 'reblock-logentry', 'SomeTitle', 'duration', '(flags)' )->plain()
			. $sep . $this->user_comment,
			'block', 'reblock',
			[
				'5::duration' => 'duration',
				'6::flags' => 'flags',
			],
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeDelete() {
		$sep = $this->context->msg( 'colon-separator' )->text();

		# delete/delete
		$this->assertIRCComment(
			$this->context->msg( 'deletedarticle', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'delete', 'delete',
			[],
			$this->user_comment
		);

		# delete/restore
		$this->assertIRCComment(
			$this->context->msg( 'undeletedarticle', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'delete', 'restore',
			[],
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeNewusers() {
		$this->assertIRCComment(
			'New user account',
			'newusers', 'newusers',
			[]
		);
		$this->assertIRCComment(
			'New user account',
			'newusers', 'create',
			[]
		);
		$this->assertIRCComment(
			'created new account SomeTitle',
			'newusers', 'create2',
			[]
		);
		$this->assertIRCComment(
			'Account created automatically',
			'newusers', 'autocreate',
			[]
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeMove() {
		$move_params = [
			'4::target' => $this->target->getPrefixedText(),
			'5::noredir' => 0,
		];
		$sep = $this->context->msg( 'colon-separator' )->text();

		# move/move
		$this->assertIRCComment(
			$this->context->msg( '1movedto2', 'SomeTitle', 'TestTarget' )
				->plain() . $sep . $this->user_comment,
			'move', 'move',
			$move_params,
			$this->user_comment
		);

		# move/move_redir
		$this->assertIRCComment(
			$this->context->msg( '1movedto2_redir', 'SomeTitle', 'TestTarget' )
				->plain() . $sep . $this->user_comment,
			'move', 'move_redir',
			$move_params,
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypePatrol() {
		# patrol/patrol
		$this->assertIRCComment(
			$this->context->msg( 'patrol-log-line', 'revision 777', '[[SomeTitle]]', '' )->plain(),
			'patrol', 'patrol',
			[
				'4::curid' => '777',
				'5::previd' => '666',
				'6::auto' => 0,
			]
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeProtect() {
		$protectParams = [
			'4::description' => '[edit=sysop] (indefinite) ‎[move=sysop] (indefinite)'
		];
		$sep = $this->context->msg( 'colon-separator' )->text();

		# protect/protect
		$this->assertIRCComment(
			$this->context->msg( 'protectedarticle', 'SomeTitle ' . $protectParams['4::description'] )
				->plain() . $sep . $this->user_comment,
			'protect', 'protect',
			$protectParams,
			$this->user_comment
		);

		# protect/unprotect
		$this->assertIRCComment(
			$this->context->msg( 'unprotectedarticle', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'protect', 'unprotect',
			[],
			$this->user_comment
		);

		# protect/modify
		$this->assertIRCComment(
			$this->context->msg(
				'modifiedarticleprotection',
				'SomeTitle ' . $protectParams['4::description']
			)->plain() . $sep . $this->user_comment,
			'protect', 'modify',
			$protectParams,
			$this->user_comment
		);

		# protect/move_prot
		$this->assertIRCComment(
			$this->context->msg( 'movedarticleprotection', 'SomeTitle', 'OldTitle' )
				->plain() . $sep . $this->user_comment,
			'protect', 'move_prot',
			[
				'4::oldtitle' => 'OldTitle'
			],
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeUpload() {
		$sep = $this->context->msg( 'colon-separator' )->text();

		# upload/upload
		$this->assertIRCComment(
			$this->context->msg( 'uploadedimage', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'upload', 'upload',
			[],
			$this->user_comment
		);

		# upload/overwrite
		$this->assertIRCComment(
			$this->context->msg( 'overwroteimage', 'SomeTitle' )->plain() . $sep . $this->user_comment,
			'upload', 'overwrite',
			[],
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeMerge() {
		$sep = $this->context->msg( 'colon-separator' )->text();

		# merge/merge
		$this->assertIRCComment(
			$this->context->msg( 'pagemerge-logentry', 'SomeTitle', 'Dest', 'timestamp' )->plain()
			. $sep . $this->user_comment,
			'merge', 'merge',
			[
				'4::dest' => 'Dest',
				'5::mergepoint' => 'timestamp',
			],
			$this->user_comment
		);
	}

	/**
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionComment
	 * @covers \MediaWiki\Logging\LogFormatter::getIRCActionText
	 */
	public function testIrcMsgForLogTypeImport() {
		$sep = $this->context->msg( 'colon-separator' )->text();

		# import/upload
		$msg = $this->context->msg( 'import-logentry-upload', 'SomeTitle' )->plain() .
			$sep .
			$this->user_comment;
		$this->assertIRCComment(
			$msg,
			'import', 'upload',
			[],
			$this->user_comment
		);

		# import/interwiki
		$msg = $this->context->msg( 'import-logentry-interwiki', 'SomeTitle' )->plain() .
			$sep .
			$this->user_comment;
		$this->assertIRCComment(
			$msg,
			'import', 'interwiki',
			[],
			$this->user_comment
		);
	}

	/**
	 * @param string $expected Expected IRC text without colors codes
	 * @param string $type Log type (move, delete, suppress, patrol ...)
	 * @param string $action A log type action
	 * @param array $params
	 * @param string|null $comment A comment for the log action
	 * @param string $msg
	 * @param bool $legacy
	 */
	protected function assertIRCComment( $expected, $type, $action, $params,
		$comment = null, $msg = '', $legacy = false
	) {
		$logEntry = new ManualLogEntry( $type, $action );
		$logEntry->setPerformer( $this->user );
		$logEntry->setTarget( $this->title );
		if ( $comment !== null ) {
			$logEntry->setComment( $comment );
		}
		$logEntry->setParameters( $params );
		$logEntry->setLegacy( $legacy );

		$formatter = $this->getServiceContainer()->getLogFormatterFactory()->newFromEntry( $logEntry );
		$formatter->setContext( $this->context );

		// Apply the same transformation as done in IRCColourfulRCFeedFormatter::getLine for rc_comment
		$ircRcComment = IRCColourfulRCFeedFormatter::cleanupForIRC( $formatter->getIRCActionComment() );

		$this->assertEquals(
			$expected,
			$ircRcComment,
			$msg
		);
	}

}
