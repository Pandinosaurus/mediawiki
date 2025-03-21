<?php

declare( strict_types = 1 );

namespace Wikimedia\Tests\FileBackend;

use Closure;
use InvalidArgumentException;
use LockManager;
use MediaWikiUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use ScopedLock;
use StatusValue;
use Wikimedia\FileBackend\FileBackend;
use Wikimedia\FileBackend\FSFile\TempFSFileFactory;
use Wikimedia\ScopedCallback;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \Wikimedia\FileBackend\FileBackend
 */
class FileBackendTest extends MediaWikiUnitTestCase {
	/**
	 * createMock() stubs out all methods, which isn't desirable for testing an abstract base class,
	 * since we often want to test that the base class calls certain methods that the derived class
	 * is meant to override. getMockBuilder() can be set to override only certain methods, but then
	 * you have to manually specify all abstract methods or else it doesn't work.
	 * getMockForAbstractClass() automatically fills in stubs for the abstract methods, but by
	 * default doesn't allow overriding any other methods. So we have to write our own.
	 *
	 * @param string|array ...$args Zero or more of the following:
	 *   - A nonempty associative array, interpreted as $config to be passed to the constructor. The
	 *     'name' and 'domainId' will be given default values if not present.
	 *   - A nonempty indexed array or a string, interpreted as a list of methods to override.
	 *   - An empty array, which is ignored.
	 * @return FileBackend|MockObject A mock with no methods overridden except those specified in
	 *   $methodsToMock, and all abstract methods.
	 */
	private function newMockFileBackend( ...$args ): FileBackend {
		$methodsToMock = [];
		$config = [];
		foreach ( $args as $arg ) {
			if ( is_string( $arg ) ) {
				$methodsToMock = [ $arg ];
			} elseif ( is_array( $arg ) ) {
				if ( isset( $arg[0] ) ) {
					$methodsToMock = $arg;
				} elseif ( $arg ) {
					$config = $arg;
				}
			} else {
				throw new InvalidArgumentException(
					'Arguments must be strings or nonempty arrays' );
			}
		}

		$config += [ 'name' => 'test_name' ];
		if ( !array_key_exists( 'wikiId', $config ) ) {
			$config += [ 'domainId' => '' ];
		}

		return $this->getMockBuilder( FileBackend::class )
			->setConstructorArgs( [ $config ] )
			->onlyMethods( $methodsToMock )
			->getMockForAbstractClass();
	}

	/**
	 * @dataProvider provideConstruct_validName
	 */
	public function testConstruct_validName( $name ): void {
		$this->newMockFileBackend( [ 'name' => $name ] );

		// No exception
		$this->assertTrue( true );
	}

	public static function provideConstruct_validName(): array {
		return [
			'simple' => [ 'foobar' ],
			'dash and underscore' => [ 'foo_bar-baz' ],
			'capital and numbers' => [ 'Duck-Car313' ],
			'255 chars' => [ str_repeat( 'a', 255 ) ],
		];
	}

	/**
	 * @dataProvider provideConstruct_invalidName
	 * @param mixed $name
	 */
	public function testConstruct_invalidName( $name ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( "Backend name '$name' is invalid." );

		$this->newMockFileBackend( [ 'name' => $name, 'domainId' => false ] );
	}

	public static function provideConstruct_invalidName(): array {
		return [
			'Empty string' => [ '' ],
			'Illegal slash' => [ 'foo/bar' ],
			'Illegal space' => [ 'foo bar' ],
			'Illegal percent' => [ 'foo%20bar' ],
			'256 chars' => [ str_repeat( 'a', 256 ) ],
			'Bang' => [ '!' ],
			'With space' => [ 'a b' ],
			'False' => [ false ],
			'Null' => [ null ],
			'Positive float' => [ 13.402 ],
			'Negative float' => [ -13.402 ],
			'True' => [ true ],
			'Positive integer' => [ 7 ],
			'Zero integer' => [ 0 ],
			'Zero float' => [ 0.0 ],
			'Negative integer' => [ -7 ],
		];
	}

	public function testConstruct_noName(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Backend name not specified' );

		$this->getMockBuilder( FileBackend::class )
			->setConstructorArgs( [ [] ] )
			->getMock();
	}

	/**
	 * @dataProvider provideConstruct_validDomainId
	 */
	public function testConstruct_validDomainId( string $domainId ): void {
		$this->newMockFileBackend( [ 'domainId' => $domainId ] );

		// No exception
		$this->assertTrue( true );
	}

	/**
	 * @dataProvider provideConstruct_validDomainId
	 */
	public function testConstruct_validWikiId( string $wikiId ): void {
		$this->newMockFileBackend( [ 'wikiId' => $wikiId ] );

		// No exception
		$this->assertTrue( true );
	}

	public static function provideConstruct_validDomainId(): array {
		return [
			'Empty string' => [ '' ],
			'1000 chars' => [ str_repeat( 'a', 1000 ) ],
			'Null character' => [ "\0" ],
			'Invalid UTF-8' => [ "\xff" ],
		];
	}

	/**
	 * @dataProvider provideConstruct_invalidDomainId
	 */
	public function testConstruct_invalidDomainId( $domainId ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( "Backend domain ID not provided for 'test_name'." );

		$this->newMockFileBackend( [ 'domainId' => $domainId ] );
	}

	public static function provideConstruct_invalidDomainId(): array {
		return [
			// We don't include null because that will fall back to wikiId
			'False' => [ false ],
			'True' => [ true ],
			'Integer' => [ 7 ],
			'Function' => [ static function () {
			} ],
			'Float' => [ -13.402 ],
			'Object' => [ (object)[] ],
			'Array' => [ [] ],
		];
	}

	/**
	 * @dataProvider provideConstruct_invalidWikiId
	 */
	public function testConstruct_invalidWikiId( $wikiId ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( "Backend domain ID not provided for 'test_name'." );

		$this->newMockFileBackend( [ 'wikiId' => $wikiId ] );
	}

	public static function provideConstruct_invalidWikiId(): array {
		return [
			'Null' => [ null ],
		] + self::provideConstruct_invalidDomainId();
	}

	public function testConstruct_noDomainId(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( "Backend domain ID not provided for 'test_name'" );

		$this->getMockBuilder( FileBackend::class )
			->setConstructorArgs( [ [ 'name' => 'test_name' ] ] )
			->getMock();
	}

	/**
	 * @dataProvider provideConstruct_properties
	 * @param string $property
	 * @param mixed $expected
	 * @param array $config Can also include the key 'inexact' to tell us to not check equality
	 *   strictly.
	 */
	public function testConstruct_properties(
		string $property, $expected, array $config = []
	): void {
		$backend = $this->newMockFileBackend( $config );

		if ( $expected instanceof Closure ) {
			$expected = $expected( $backend );
		}

		$assertMethod = isset( $config['inexact'] ) ? 'assertEquals' : 'assertSame';
		unset( $config['inexact'] );

		// We need to test this for the sake of subclasses that actually use the property. There
		// doesn't seem to be any better way to do it. It shouldn't be tested in the subclasses,
		// because we're testing the behavior of this class' constructor. We could make our own
		// subclass, but we'd have to stub 26 abstract methods.
		$this->$assertMethod( $expected,
			TestingAccessWrapper::newFromObject( $backend )->$property );
	}

	public static function provideConstruct_properties(): array {
		$tmpFileFactory = new TempFSFileFactory( 'some_unique_path' );

		return [
			'parallelize default value' => [ 'parallelize', 'off' ],
			'parallelize null' => [ 'parallelize', 'off', [ 'parallelize' => null ] ],
			'parallelize cast to string' => [ 'parallelize', '1', [ 'parallelize' => true ] ],
			'parallelize case-preserving' =>
				[ 'parallelize', 'iMpLiCiT', [ 'parallelize' => 'iMpLiCiT' ] ],

			'concurrency default value' => [ 'concurrency', 50 ],
			'concurrency null' => [ 'concurrency', 50, [ 'concurrency' => null ] ],
			'concurrency cast to int' => [ 'concurrency', 51, [ 'concurrency' => '51x' ] ],

			'obResetFunc default value' =>
				[ 'obResetFunc', [ FileBackend::class, 'resetOutputBufferTheDefaultWay' ] ],
			'obResetFunc null' => [
				'obResetFunc',
				[ FileBackend::class, 'resetOutputBufferTheDefaultWay' ],
				[ 'obResetFunc' => null ]
			],
			'obResetFunc set' => [
				'obResetFunc',
				'wfSomeImaginaryFunction',
				[ 'obResetFunc' => 'wfSomeImaginaryFunction' ]
			],

			'headerFunc default value' => [ 'headerFunc', 'header' ],
			'headerFunc set' => [ 'headerFunc', 'myHeaderFunc', [ 'headerFunc' => 'myHeaderFunc' ] ],

			'profiler default value' => [ 'profiler', null ],
			'profiler not callable' => [ 'profiler', null, [ 'profiler' => '!' ] ],

			'logger default value' => [ 'logger', new NullLogger, [ 'inexact' => true ] ],
			'logger set' => [ 'logger', 'abcd', [ 'logger' => 'abcd' ] ],

			'statusWrapper default value' => [ 'statusWrapper', null ],

			'tmpFileFactory default value' =>
				[ 'tmpFileFactory', new TempFSFileFactory, [ 'inexact' => true ] ],
			'tmpDirectory null' => [ 'tmpFileFactory', new TempFSFileFactory,
				[ 'tmpDirectory' => null, 'inexact' => true ] ],
			'tmpDirectory set' => [ 'tmpFileFactory', new TempFSFileFactory( 'dir' ),
				[ 'tmpDirectory' => 'dir', 'inexact' => true ] ],
			'tmpFileFactory null' => [ 'tmpFileFactory', new TempFSFileFactory,
				[ 'tmpFileFactory' => null, 'inexact' => true ] ],
			'tmpFileFactory set' => [ 'tmpFileFactory', $tmpFileFactory,
				[ 'tmpFileFactory' => $tmpFileFactory ] ],
			'tmpDirectory and tmpFileFactory set' => [
				'tmpFileFactory',
				new TempFSFileFactory( 'dir' ),
				[ 'tmpDirectory' => 'dir', 'tmpFileFactory' => $tmpFileFactory, 'inexact' => true ],
			],
			'tmpDirectory null and tmpFileFactory set' => [ 'tmpFileFactory', $tmpFileFactory,
				[ 'tmpDirectory' => null, 'tmpFileFactory' => $tmpFileFactory ] ],
		];
	}

	public function testSetLogger(): void {
		$backend = $this->newMockFileBackend();
		$logger = new NullLogger;
		// See comment in testConstruct_properties about use of TestingAccessWrapper.
		$this->assertNotSame( $logger, TestingAccessWrapper::newFromObject( $backend )->logger );
		$backend->setLogger( $logger );
		$this->assertSame( $logger, TestingAccessWrapper::newFromObject( $backend )->logger );
	}

	public function testGetName(): void {
		$backend = $this->newMockFileBackend();
		$this->assertSame( 'test_name', $backend->getName() );
	}

	/**
	 * @dataProvider provideGetDomainId
	 */
	public function testGetDomainId( array $config ): void {
		$backend = $this->newMockFileBackend( $config );
		$this->assertSame( 'test_domain', $backend->getDomainId() );
	}

	/**
	 * @dataProvider provideGetDomainId
	 */
	public function testGetWikiId( array $config ): void {
		$backend = $this->newMockFileBackend( $config );
		$this->assertSame( 'test_domain', $backend->getWikiId() );
	}

	public static function provideGetDomainId(): array {
		return [
			'Only domainId' => [ [ 'domainId' => 'test_domain' ] ],
			'Only wikiId' => [ [ 'wikiId' => 'test_domain' ] ],
			'null domainId' => [ [ 'domainId' => null, 'wikiId' => 'test_domain' ] ],
			'wikiId is ignored if domainId is present' =>
				[ [ 'domainId' => 'test_domain', 'wikiId' => 'other_domain' ] ],
		];
	}

	public function testIsReadOnly_default(): void {
		$backend = $this->newMockFileBackend();
		$this->assertFalse( $backend->isReadOnly() );
		$this->assertFalse( $backend->getReadOnlyReason() );
	}

	public function testIsReadOnly(): void {
		$backend = $this->newMockFileBackend( [ 'readOnly' => '.' ] );
		$this->assertTrue( $backend->isReadOnly() );
		$this->assertSame( '.', $backend->getReadOnlyReason() );
	}

	public function testGetFeatures(): void {
		$backend = $this->newMockFileBackend();
		$this->assertSame( FileBackend::ATTR_UNICODE_PATHS, $backend->getFeatures() );
	}

	/**
	 * @dataProvider provideHasFeatures
	 */
	public function testHasFeatures(
		bool $expected, int $actualFeatures, int $testedFeatures
	): void {
		$backend = $this->createMock( FileBackend::class );
		$backend->method( 'getFeatures' )->willReturn( $actualFeatures );

		$this->assertSame( $expected, $backend->hasFeatures( $testedFeatures ) );
	}

	public static function provideHasFeatures(): array {
		return [
			'Nothing has nothing' => [ true, 0, 0 ],
			"Nothing doesn't have something" => [ false, 0, 1 ],
			'Something has nothing' => [ true, 1, 0 ],
			'Something has itself' => [ true, 1, 1 ],
			"Something doesn't have something else" => [ false, 0b01, 0b10 ],
			"Something doesn't have itself and something else" => [ false, 0b01, 0b11 ],
			'Two things have the first one' => [ true, 0b11, 0b01 ],
			'Two things have the second one' => [ true, 0b11, 0b10 ],
			'Two things have both' => [ true, 0b11, 0b11 ],
			"Two things don't have a third" => [ false, 0b11, 0b100 ],
		];
	}

	/**
	 * @dataProvider provideReadOnly
	 */
	public function testReadOnly( string $method ): void {
		$backend = $this->newMockFileBackend( [ 'readOnly' => '.' ] );
		$status = $backend->$method( [] );
		$this->assertStatusError( 'backend-fail-readonly', $status );
	}

	public static function provideReadOnly(): array {
		return [
			'doOperations' => [ 'doOperations', 'doOperationsInternal', [ [ [] ] ] ],
			'doOperation' => [ 'doOperation', 'doOperationsInternal', [ [ 'op' => '' ] ] ],
			'doQuickOperations' => [ 'doQuickOperations', 'doQuickOperationsInternal', [ [ [] ] ] ],
			'doQuickOperation' => [
				'doQuickOperation',
				'doQuickOperationsInternal',
				[ [ 'op' => '' ] ]
			],
			'prepare' => [ 'prepare', 'doPrepare' ],
			'secure' => [ 'secure', 'doSecure' ],
			'publish' => [ 'publish', 'doPublish' ],
			'clean' => [ 'clean', 'doClean' ],
		];
	}

	/**
	 * @dataProvider provideReadOnly
	 * @param string $method Method to call
	 * @param string $internalMethod Internal method the call will be forwarded to
	 * @param array $args To be passed to $method before a final argument of
	 *   [ 'bypassReadOnly' => true ]
	 */
	public function testDoOperations_bypassReadOnly(
		string $method, string $internalMethod, array $args = []
	): void {
		$backend = $this->newMockFileBackend( [ 'readOnly' => '.' ], $internalMethod );
		$backend->expects( $this->once() )->method( $internalMethod )
			->willReturn( StatusValue::newGood( 'myvalue' ) );

		$status = $backend->$method( ...array_merge( $args, [ [ 'bypassReadOnly' => true ] ] ) );

		$this->assertStatusGood( $status );
		$this->assertStatusValue( 'myvalue', $status );
	}

	/**
	 * @dataProvider provideDoMultipleOperations
	 */
	public function testDoOperations_noOp( string $method ): void {
		$backend = $this->newMockFileBackend(
			[ 'doOperationsInternal', 'doQuickOperationsInternal' ] );
		$backend->expects( $this->never() )->method( 'doOperationsInternal' );
		$backend->expects( $this->never() )->method( 'doQuickOperationsInternal' );

		$status = $backend->$method( [] );
		$this->assertStatusGood( $status );
	}

	public static function provideDoMultipleOperations(): array {
		return [
			'doOperations' => [ 'doOperations' ],
			'doQuickOperations' => [ 'doQuickOperations' ],
		];
	}

	/**
	 * @dataProvider provideDoOperations
	 * @param string $method 'doOperation' or 'doOperations'
	 */
	public function testDoOperations_nonLockingNoForce( string $method ): void {
		$backend = $this->newMockFileBackend( [ 'doOperationsInternal' ] );
		$backend->expects( $this->once() )->method( 'doOperationsInternal' )
			->with( [ [] ], [] );
		$backend->$method( $method === 'doOperation' ? [] : [ [] ], [ 'nonLocking' => true ] );
	}

	public static function provideDoOperations(): array {
		return [
			'doOperations' => [ 'doOperations' ],
			'doOperation' => [ 'doOperation' ],
		];
	}

	/**
	 * @dataProvider provideDoOperations
	 * @param string $method 'doOperation' or 'doOperations'
	 */
	public function testDoOperations_nonLockingForce( string $method ): void {
		$backend = $this->newMockFileBackend( [ 'doOperationsInternal' ] );
		$backend->expects( $this->once() )->method( 'doOperationsInternal' )
			->with( [ [] ], [ 'nonLocking' => true, 'force' => true ] );
		$backend->$method(
			$method === 'doOperation' ? [] : [ [] ],
			[ 'nonLocking' => true, 'force' => true ]
		);
	}

	// XXX Can't test newScopedIgnoreUserAbort() because it's a no-op in CLI

	/**
	 * @dataProvider provideAction
	 * @param string $prefix '' or 'quick'
	 * @param string $action
	 */
	public function testAction( string $prefix, string $action ): void {
		$backend = $this->newMockFileBackend( 'do' . ucfirst( $prefix ) . 'OperationsInternal' );
		$expectedOp = [ 'op' => $action, 'foo' => 'bar' ];
		if ( $prefix === 'quick' ) {
			$expectedOp['overwrite'] = true;
		}
		$backend->expects( $this->once() )
			->method( 'do' . ucfirst( $prefix ) . 'OperationsInternal' )
			->with( [ $expectedOp ], [ 'baz' => 'quuz' ] )
			->willReturn( StatusValue::newGood( 'myvalue' ) );

		$method = $prefix ? $prefix . ucfirst( $action ) : $action;
		$status = $backend->$method( [ 'op' => 'ignored', 'foo' => 'bar' ], [ 'baz' => 'quuz' ] );

		$this->assertStatusOK( $status );
		$this->assertStatusValue( 'myvalue', $status );
	}

	public static function provideAction(): array {
		$ret = [];
		foreach ( [ '', 'quick' ] as $prefix ) {
			foreach ( [ 'create', 'store', 'copy', 'move', 'delete', 'describe' ] as $action ) {
				$key = $prefix ? $prefix . ucfirst( $action ) : $action;
				$ret[$key] = [ $prefix, $action ];
			}
		}
		return $ret;
	}

	/**
	 * @dataProvider provideForwardToDo
	 */
	public function testForwardToDo( string $method ): void {
		$backend = $this->newMockFileBackend( 'do' . ucfirst( $method ) );
		$backend->expects( $this->once() )->method( 'do' . ucfirst( $method ) )
			->with( [ 'foo' => 'bar' ] )
			->willReturn( StatusValue::newGood( 'myvalue' ) );

		$status = $backend->$method( [ 'foo' => 'bar' ] );

		$this->assertStatusGood( $status );
		$this->assertStatusValue( 'myvalue', $status );
	}

	public static function provideForwardToDo(): array {
		return [
			'prepare' => [ 'prepare' ],
			'secure' => [ 'secure' ],
			'publish' => [ 'publish' ],
			'clean' => [ 'clean' ],
		];
	}

	/**
	 * @dataProvider provideForwardToMulti
	 */
	public function testForwardToMulti( string $method ): void {
		$backend = $this->newMockFileBackend( "{$method}Multi" );
		$backend->expects( $this->once() )->method( "{$method}Multi" )
			->with( [ 'srcs' => [ 'mysrc' ], 'foo' => 'bar', 'src' => 'mysrc' ] )
			->willReturn( [ 'mysrc' => 'mycontents' ] );

		$result = $backend->$method( [ 'srcs' => 'ignored', 'foo' => 'bar', 'src' => 'mysrc' ] );

		$this->assertSame( 'mycontents', $result );
	}

	public static function provideForwardToMulti(): array {
		return [
			'getFileContents' => [ 'getFileContents' ],
			'getLocalReference' => [ 'getLocalReference' ],
			'getLocalCopy' => [ 'getLocalCopy' ],
		];
	}

	/**
	 * @dataProvider provideForwardFromTop
	 */
	public function testForwardFromTop( string $methodSuffix ): void {
		$backend = $this->newMockFileBackend( "get$methodSuffix" );
		$backend->expects( $this->once() )->method( "get$methodSuffix" )
			->with( [ 'topOnly' => true, 'foo' => 'bar' ] )
			->willReturn( [ 'something' ] );

		$method = "getTop$methodSuffix";
		$result = $backend->$method( [ 'topOnly' => 'ignored', 'foo' => 'bar' ] );

		$this->assertSame( [ 'something' ], $result );
	}

	public static function provideForwardFromTop(): array {
		return [
			'getTopDirectoryList' => [ 'DirectoryList' ],
			'getTopFileList' => [ 'FileList' ],
		];
	}

	/**
	 * @dataProvider provideLockUnlockFiles
	 * @param string $method
	 * @param int|null $timeout Only relevant for lockFiles
	 */
	public function testLockUnlockFiles( string $method, ?int $timeout = null ): void {
		$args = [ [ 'mwstore://a/b/', 'mwstore://c/d//e' ], LockManager::LOCK_SH ];

		$mockLm = $this->getMockBuilder( LockManager::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'do' . ucfirst( $method ) . 'ByType', 'doLock', 'doUnlock' ] )
			->getMock();
		// XXX PHPUnit can't override final methods (T231419)
		//$mockLm->expects( $this->once() )->method( $method )
		//	->with( ...array_merge( $args, [ $timeout ?? 0 ] ) )
		//	->willReturn( StatusValue::newGood( 'myvalue' ) );
		//$mockLm->expects( $this->never() )->method( $this->anythingBut( $method ) );
		$mockLm->expects( $this->once() )->method( 'do' . ucfirst( $method ) . 'ByType' )
			->with( [ LockManager::LOCK_SH => [ 'mwstore://a/b', 'mwstore://c/d/e' ] ] )
			->willReturn( StatusValue::newGood( 'myvalue' ) );

		$backend = $this->newMockFileBackend( [ 'lockManager' => $mockLm ] );
		$backendMethod = "{$method}Files";

		$status = $backend->$backendMethod( ...array_merge( $args, (array)$timeout ) );

		$this->assertStatusGood( $status );
		$this->assertStatusValue( 'myvalue', $status );
	}

	public static function provideLockUnlockFiles(): array {
		return [
			[ 'lock' ],
			[ 'lock', 731 ],
			[ 'unlock' ],
		];
	}

	/**
	 * @dataProvider provideGetScopedFileLocks
	 * @param array $paths
	 * @param int|string $type
	 * @param array $expectedPathsByType Expected to be passed to the LockManager
	 * @param StatusValue $lockStatus Returned from doLockByType()
	 * @param StatusValue|null $unlockStatus Returned from doUnlockByType() (if locking succeeded)
	 */
	public function testGetScopedFileLocks(
		array $paths, $type, array $expectedPathsByType, StatusValue $lockStatus,
		?StatusValue $unlockStatus = null
	): void {
		$mockLm = $this->getMockBuilder( LockManager::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'doLockByType', 'doUnlockByType', 'doLock', 'doUnlock' ] )
			->getMock();
		$mockLm->expects( $this->once() )->method( 'doLockByType' )
			->with( $expectedPathsByType )
			->willReturn( $lockStatus );
		$mockLm->expects( $this->exactly( $unlockStatus ? 1 : 0 ) )->method( 'doUnlockByType' )
			->with( $expectedPathsByType )
			->willReturn( $unlockStatus );

		$backend = $this->newMockFileBackend( [ 'lockManager' => $mockLm ] );

		$status = StatusValue::newGood( 'myvalue' );
		$scopedLock = $backend->getScopedFileLocks( $paths, $type, $status );

		$this->assertStatusValue( 'myvalue', $status );
		$this->assertSame( $lockStatus->isOK(), $status->isOK() );
		$this->assertStatusMessagesExactly( $lockStatus, $status );

		if ( !$lockStatus->isOK() ) {
			$this->assertNull( $scopedLock );
			return;
		}

		$this->assertInstanceOf( ScopedLock::class, $scopedLock );
		unset( $scopedLock );

		$this->assertStatusValue( 'myvalue', $status );
		$this->assertSame( $lockStatus->isOK(), $status->isOK() );
		$this->assertStatusMessagesExactly( $lockStatus->merge( $unlockStatus ), $status );
	}

	public static function provideGetScopedFileLocks(): array {
		return [
			'Simple successful shared lock' => [
				[ 'mwstore://a/b/' ], LockManager::LOCK_SH,
				[ LockManager::LOCK_SH => [ 'mwstore://a/b' ] ],
				StatusValue::newGood( 'value2' ), StatusValue::newGood( 'value3' ),
			],
			'Mixed lock' => [
				[ LockManager::LOCK_SH => [ 'mwstore://a/b/' ],
					LockManager::LOCK_EX => [ 'mwstore://c/d//e' ] ], 'mixed',
				[ LockManager::LOCK_SH => [ 'mwstore://a/b' ],
					LockManager::LOCK_EX => [ 'mwstore://c/d/e' ] ],
				StatusValue::newGood(), StatusValue::newGood(),
			],
			'Mixed with only shared locks' => [
				[ LockManager::LOCK_SH => [ 'mwstore://a/b/', 'mwstore://c/d//e' ] ], 'mixed',
				[ LockManager::LOCK_SH => [ 'mwstore://a/b', 'mwstore://c/d/e' ] ],
				StatusValue::newGood(), StatusValue::newGood(),
			],
			'Locking error' => [
				[ 'mwstore://a/b/' ], LockManager::LOCK_EX,
				[ LockManager::LOCK_EX => [ 'mwstore://a/b' ] ],
				StatusValue::newFatal( 'XXX' ),
			],
			'Unlocking error' => [
				[ 'mwstore://a/b/', 'mwstore://c/d//e' ], LockManager::LOCK_EX,
				[ LockManager::LOCK_EX => [ 'mwstore://a/b', 'mwstore://c/d/e' ] ],
				StatusValue::newGood(), StatusValue::newFatal( 'XXXX' ),
			],
		];
	}

	/**
	 * @dataProvider provideConstruct_validName
	 * @param mixed $name
	 */
	public function testGetRootStoragePath( $name ): void {
		$backend = $this->newMockFileBackend( [ 'name' => $name ] );
		$this->assertSame( "mwstore://$name", $backend->getRootStoragePath() );
	}

	/**
	 * @dataProvider provideConstruct_validName
	 */
	public function testGetContainerStoragePath( $name ): void {
		$backend = $this->newMockFileBackend( [ 'name' => $name ] );
		$this->assertSame( "mwstore://$name/mycontainer",
			$backend->getContainerStoragePath( 'mycontainer' ) );
	}

	/**
	 * @dataProvider provideIsStoragePath
	 */
	public function testIsStoragePath( string $path, bool $expected ): void {
		$this->assertSame( $expected, FileBackend::isStoragePath( $path ) );
	}

	public static function provideIsStoragePath(): array {
		$paths = [
			'mwstore://' => true,
			'mwstore://backend' => true,
			'mwstore://backend/container' => true,
			'mwstore://backend/container/' => true,
			'mwstore://backend/container/path' => true,
			'mwstore://backend//container/' => true,
			'mwstore://backend//container//' => true,
			'mwstore://backend//container//path' => true,
			'mwstore:///' => true,
			'mwstore:/' => false,
			'mwstore:' => false,
		];
		$ret = [];
		foreach ( $paths as $path => $expected ) {
			$ret[$path] = [ $path, $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideSplitStoragePath
	 */
	public function testSplitStoragePath( string $path, array $expected ): void {
		$this->assertSame( $expected, FileBackend::splitStoragePath( $path ) );
	}

	public static function provideSplitStoragePath(): array {
		$paths = [
			'mwstore://backend/container' => [ 'backend', 'container', '' ],
			'mwstore://backend/container/' => [ 'backend', 'container', '' ],
			'mwstore://backend/container/path' => [ 'backend', 'container', 'path' ],
			'mwstore://backend/container//path' => [ 'backend', 'container', '/path' ],
			'mwstore://backend//container/path' => [ null, null, null ],
			'mwstore://backend//container' => [ null, null, null ],
			'mwstore://backend//container//path' => [ null, null, null ],
			'mwstore://' => [ null, null, null ],
			'mwstore://backend' => [ null, null, null ],
			'mwstore:///' => [ null, null, null ],
			'mwstore:/' => [ null, null, null ],
			'mwstore:' => [ null, null, null ],
		];
		$ret = [];
		foreach ( $paths as $path => $expected ) {
			$ret[$path] = [ $path, $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideNormalizeStoragePath
	 * @param string $path
	 * @param string|null $expected
	 */
	public function testNormalizeStoragePath( string $path, ?string $expected ): void {
		$this->assertSame( $expected, FileBackend::normalizeStoragePath( $path ) );
	}

	public static function provideNormalizeStoragePath(): array {
		$paths = [
			'mwstore://backend/container' => 'mwstore://backend/container',
			'mwstore://backend/container/' => 'mwstore://backend/container',
			'mwstore://backend/container/path' => 'mwstore://backend/container/path',
			'mwstore://backend/container//path' => 'mwstore://backend/container/path',
			'mwstore://backend/container///path' => 'mwstore://backend/container/path',
			'mwstore://backend/container///path//to///obj' =>
				'mwstore://backend/container/path/to/obj',
			'mwstore://' => null,
			'mwstore://backend' => null,
			'mwstore://backend//container' => null,
			'mwstore://backend//container/path' => null,
			'mwstore://backend//container//path' => null,
			'mwstore:///' => null,
			'mwstore:/' => null,
			'mwstore:' => null,
		];
		$ret = [];
		foreach ( $paths as $path => $expected ) {
			$ret[$path] = [ $path, $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideParentStoragePath
	 * @param string $path
	 * @param string|null $expected
	 */
	public function testParentStoragePath( string $path, ?string $expected ): void {
		$this->assertSame( $expected, FileBackend::parentStoragePath( $path ) );
	}

	public static function provideParentStoragePath(): array {
		$paths = [
			'mwstore://backend/container/path/to/obj' => 'mwstore://backend/container/path/to',
			'mwstore://backend/container/path/to' => 'mwstore://backend/container/path',
			'mwstore://backend/container/path' => 'mwstore://backend/container',
			'mwstore://backend/container' => null,
			'mwstore://backend/container/path/to/obj/' => 'mwstore://backend/container/path/to',
			'mwstore://backend/container/path/to/' => 'mwstore://backend/container/path',
			'mwstore://backend/container/path/' => 'mwstore://backend/container',
			'mwstore://backend/container/' => null,
		];
		$ret = [];
		foreach ( $paths as $path => $expected ) {
			$ret[$path] = [ $path, $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideExtensionFromPath
	 */
	public function testExtensionFromPath( array $args, string $expected ): void {
		$this->assertSame( $expected, FileBackend::extensionFromPath( ...$args ) );
	}

	public static function provideExtensionFromPath(): array {
		$paths = [
			'mwstore://backend/container/path.Txt' => 'Txt',
			'mwstore://backend/container/path.svg.pNG' => 'pNG',
			'mwstore://backend/container/path' => '',
			'mwstore://backend/container/path.' => '',
		];
		$ret = [];
		foreach ( $paths as $path => $expected ) {
			$ret[$path] = [ [ $path ], strtolower( $expected ) ];
			$ret["$path (lowercase)"] = [ [ $path, 'lowercase' ], strtolower( $expected ) ];
			$ret["$path (uppercase)"] = [ [ $path, 'uppercase' ], strtoupper( $expected ) ];
			$ret["$path (rawcase)"] = [ [ $path, 'rawcase' ], $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideIsPathTraversalFree
	 */
	public function testIsPathTraversalFree( string $path, bool $expected ): void {
		$this->assertSame( $expected, FileBackend::isPathTraversalFree( $path ) );
	}

	public static function provideIsPathTraversalFree(): array {
		$traversalFree = [
			'a\\b',
			'a//b',
			'/a',
			'\\a//b/',
		];

		$hasTraversal = [];

		$strippedPrefixes = [ '', '/', '//', '///', '\\', '\\\\', '\\\\\\' ];
		$unstrippedPrefixes = [ '.', 'a', 'a/', '/a', ' ', "\0" ];
		$suffixes = [ '', '.', 'a', '/', '/a', ' ', "\0" ];

		foreach ( [ '.', '..' ] as $basePath ) {
			foreach ( $strippedPrefixes as $prefix ) {
				foreach ( $suffixes as $suffix ) {
					if ( $suffix === '' ) {
						$hasTraversal[] = "$prefix$basePath";
					} else {
						$traversalFree[] = "$prefix$basePath$suffix";
					}
				}
			}
			foreach ( $unstrippedPrefixes as $prefix ) {
				foreach ( $suffixes as $suffix ) {
					$traversalFree[] = "$prefix$basePath$suffix";
				}
			}
		}

		foreach ( [ './', '.\\', '../', '..\\' ] as $basePath ) {
			foreach ( $strippedPrefixes as $prefix ) {
				foreach ( $suffixes as $suffix ) {
					$hasTraversal[] = "$prefix$basePath$suffix";
				}
			}
			foreach ( $unstrippedPrefixes as $prefix ) {
				foreach ( $suffixes as $suffix ) {
					$traversalFree[] = "$prefix$basePath$suffix";
				}
			}
		}

		foreach (
			[ '/./', '\\./', '/.\\', '\\.\\', '/../', '\\../', '/..\\', '\\..\\' ] as $basePath
		) {
			foreach ( array_merge( $strippedPrefixes, $unstrippedPrefixes ) as $prefix ) {
				foreach ( $suffixes as $suffix ) {
					$hasTraversal[] = "$prefix$basePath$suffix";
				}
			}
		}

		// Some things might be traversal-free vis-a-vis one base path but a traversal for another
		$traversalFree = array_diff( $traversalFree, $hasTraversal );

		$ret = [];
		foreach ( $traversalFree as $path ) {
			$ret[$path] = [ $path, true ];
		}
		foreach ( $hasTraversal as $path ) {
			$ret[$path] = [ $path, false ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideMakeContentDisposition
	 */
	public function testMakeContentDisposition( array $args, string $expected ): void {
		$this->assertSame( $expected, FileBackend::makeContentDisposition( ...$args ) );
	}

	public static function provideMakeContentDisposition(): array {
		$tests = [
			[ [ 'inline' ], 'inline' ],
			[ [ 'inLINE' ], 'inline' ],
			[ [ 'inLINE', '' ], 'inline' ],
			[ [ 'attachment' ], 'attachment' ],
			[ [ 'atTACHment' ], 'attachment' ],
			[ [ 'atTACHment', '' ], 'attachment' ],

			[ [ 'inline', 'filename.txt' ], "inline;filename*=UTF-8''filename.txt" ],
			[ [ 'attachment', 'filename.txt' ], "attachment;filename*=UTF-8''filename.txt" ],

			[ [ 'inline', 'path/filename!!!' ], "inline;filename*=UTF-8''filename%21%21%21" ],
		];
		$ret = [];
		foreach ( $tests as [ $args, $expected ] ) {
			$ret[implode( ', ', $args )] = [ $args, $expected ];
		}
		return $ret;
	}

	/**
	 * @dataProvider provideMakeContentDisposition_invalid
	 */
	public function testMakeContentDisposition_invalid( string ...$args ): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( "Invalid Content-Disposition type '{$args[0]}'." );

		FileBackend::makeContentDisposition( ...$args );
	}

	public static function provideMakeContentDisposition_invalid(): array {
		return [
			[ 'foo' ],
			[ 'foo', '' ],
			[ 'foo', 'bar' ],
			[ ' inline' ],
			[ 'inline ' ],
		];
	}

	/**
	 * @dataProvider provideDoOperations
	 * @param string $method 'doOperation' or 'doOperations'
	 */
	public function testResolveFSFileObjects( string $method ): void {
		$tmpFile = ( new TempFSFileFactory )->newTempFSFile( 'a' );

		$backend = $this->newMockFileBackend( 'doOperationsInternal' );
		$backend->expects( $this->once() )->method( 'doOperationsInternal' )
			->with( [ [ 'src' => $tmpFile->getPath(), 'srcRef' => $tmpFile ] ] )
			->willReturn( StatusValue::newGood() );

		$op = [ 'src' => $tmpFile ];
		if ( $method === 'doOperations' ) {
			$op = [ $op ];
		}
		$status = $backend->$method( $op );

		$this->assertStatusGood( $status );
	}

	/**
	 * @dataProvider provideDoOperations
	 * @param string $method 'doOperation' or 'doOperations'
	 */
	public function testResolveFSFileObjects_preservesTempFiles( string $method ): void {
		$tmpFile = ( new TempFSFileFactory )->newTempFSFile( 'a' );
		$path = $tmpFile->getPath();

		$backend = $this->newMockFileBackend();

		$op = [ 'src' => $tmpFile ];
		if ( $method === 'doOperations' ) {
			$op = [ $op ];
		}
		$status = $backend->$method( $op );

		$this->assertTrue( is_file( $path ) );
	}

	public function testWrapStatus(): void {
		$expectedSv = StatusValue::newGood( 'myvalue' );
		$backend = $this->newMockFileBackend( [ 'statusWrapper' =>
			function ( StatusValue $sv ) use ( $expectedSv ): StatusValue {
				$this->assertEquals( StatusValue::newGood(), $sv );
				return $expectedSv;
			}
		] );
		$this->assertSame( $expectedSv, $backend->doOperations( [] ) );
	}

	public function testScopedProfileSection(): void {
		$scopedCallback = new ScopedCallback( static function () {
		} );
		$backend = $this->newMockFileBackend( [ 'profiler' =>
			function ( string $section ) use ( $scopedCallback ): ScopedCallback {
				$this->assertSame( 'mysection', $section );
				return $scopedCallback;
			}
		] );
		// See comment in testConstruct_properties about use of TestingAccessWrapper.
		$this->assertSame( $scopedCallback,
			TestingAccessWrapper::newFromObject( $backend )->scopedProfileSection( 'mysection' ) );
	}
}
