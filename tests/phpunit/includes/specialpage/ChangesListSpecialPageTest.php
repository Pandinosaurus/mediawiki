<?php

namespace MediaWiki\Tests\SpecialPage;

use MediaWiki\Context\RequestContext;
use MediaWiki\MainConfigNames;
use MediaWiki\RecentChanges\ChangesListBooleanFilterGroup;
use MediaWiki\RecentChanges\ChangesListStringOptionsFilterGroup;
use MediaWiki\Request\FauxRequest;
use MediaWiki\SpecialPage\ChangesListSpecialPage;
use MediaWiki\Tests\User\TempUser\TempUserTestTrait;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IExpression;
use Wikimedia\TestingAccessWrapper;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * Test class for ChangesListSpecialPage class
 *
 * Copyright © 2011-, Antoine Musso, Stephane Bisson, Matthew Flaschen
 *
 * @author Antoine Musso
 * @author Stephane Bisson
 * @author Matthew Flaschen
 * @group Database
 *
 * @covers \MediaWiki\SpecialPage\ChangesListSpecialPage
 */
class ChangesListSpecialPageTest extends AbstractChangesListSpecialPageTestCase {

	use TempUserTestTrait {
		enableAutoCreateTempUser as _enableAutoCreateTempUser;
		disableAutoCreateTempUser as _disableAutoCreateTempUser;
	}

	protected function setUp(): void {
		$this->overrideConfigValue(
			MainConfigNames::GroupPermissions,
			[ '*' => [ 'edit' => true ] ]
		);
		parent::setUp();
		$this->clearHooks();
	}

	/**
	 * @return ChangesListSpecialPage
	 */
	protected function getPageAccessWrapper() {
		$mock = $this->getMockBuilder( ChangesListSpecialPage::class )
			->setConstructorArgs(
				[
					'ChangesListSpecialPage',
					'',
					$this->getServiceContainer()->getUserIdentityUtils(),
					$this->getServiceContainer()->getTempUserConfig()
				]
			)
			->onlyMethods( [ 'getPageTitle' ] )
			->getMockForAbstractClass();

		$mock->method( 'getPageTitle' )->willReturn(
			Title::makeTitle( NS_SPECIAL, 'ChangesListSpecialPage' )
		);

		$mock = TestingAccessWrapper::newFromObject(
			$mock
		);

		return $mock;
	}

	private function buildQuery(
		array $requestOptions,
		?User $user = null
	): array {
		$context = new RequestContext;
		$context->setRequest( new FauxRequest( $requestOptions ) );
		if ( $user ) {
			$context->setUser( $user );
		}

		$this->changesListSpecialPage->setContext( $context );
		$formOptions = $this->changesListSpecialPage->setup( null );

		# Filter out rc_timestamp conditions which depends on the test runtime
		# This condition is not needed as of march 2, 2011 -- hashar
		# @todo FIXME: Find a way to generate the correct rc_timestamp

		$tables = [];
		$fields = [];
		$queryConditions = [];
		$query_options = [];
		$join_conds = [];

		call_user_func_array(
			[ $this->changesListSpecialPage, 'buildQuery' ],
			[
				&$tables,
				&$fields,
				&$queryConditions,
				&$query_options,
				&$join_conds,
				$formOptions
			]
		);

		$queryConditions = array_filter(
			$queryConditions,
			[ self::class, 'filterOutRcTimestampCondition' ]
		);

		return $queryConditions;
	}

	/**
	 * helper to test SpecialRecentchanges::buildQuery()
	 * @param array $expected
	 * @param array $requestOptions
	 * @param string $message
	 * @param User|null $user
	 */
	private function assertConditions(
		array $expected,
		array $requestOptions,
		string $message,
		?User $user = null
	) {
		$queryConditions = $this->buildQuery( $requestOptions, $user );

		$this->assertEquals(
			$this->normalizeCondition( $expected ),
			$this->normalizeCondition( $queryConditions ),
			$message
		);
	}

	private function normalizeCondition( array $conds ): array {
		$dbr = $this->getDb();
		$normalized = array_map(
			static function ( $k, $v ) use ( $dbr ) {
				if ( is_array( $v ) ) {
					sort( $v );
				}
				// (Ab)use makeList() to format only this entry
				return $dbr->makeList( [ $k => $v ], Database::LIST_AND );
			},
			array_keys( $conds ),
			$conds
		);
		sort( $normalized );
		return $normalized;
	}

	/**
	 * @param array|string|IExpression $var
	 * @return bool false if condition begins with 'rc_timestamp '
	 */
	private static function filterOutRcTimestampCondition( $var ): bool {
		if ( $var instanceof IExpression ) {
			$var = $var->toGeneralizedSql();
		}
		return is_array( $var ) || !str_contains( (string)$var, 'rc_timestamp ' );
	}

	public function testRcNsFilter() {
		$this->assertConditions(
			[ # expected
				'rc_namespace = 0',
			],
			[
				'namespace' => NS_MAIN,
			],
			"rc conditions with one namespace"
		);
	}

	public function testRcNsFilterInversion() {
		$this->assertConditions(
			[ # expected
				'rc_namespace != 0',
			],
			[
				'namespace' => NS_MAIN,
				'invert' => 1,
			],
			"rc conditions with namespace inverted"
		);
	}

	public function testRcNsFilterMultiple() {
		$this->assertConditions(
			[ # expected
				'rc_namespace IN (1,2,3)',
			],
			[
				'namespace' => '1;2;3',
			],
			"rc conditions with multiple namespaces"
		);
	}

	public function testRcNsFilterMultipleAssociated() {
		$this->assertConditions(
			[ # expected
				'rc_namespace IN (0,1,4,5,6,7)',
			],
			[
				'namespace' => '1;4;7',
				'associated' => 1,
			],
			"rc conditions with multiple namespaces and associated"
		);
	}

	public function testRcNsFilterAssociatedSpecial() {
		$this->assertConditions(
			[ # expected
				'rc_namespace IN (-1,0,1)',
			],
			[
				'namespace' => '1;-1',
				'associated' => 1,
			],
			"rc conditions with associated and special namespace"
		);
	}

	public function testRcNsFilterMultipleAssociatedInvert() {
		$this->assertConditions(
			[ # expected
				'rc_namespace NOT IN (2,3,8,9)',
			],
			[
				'namespace' => '2;3;9',
				'associated' => 1,
				'invert' => 1
			],
			"rc conditions with multiple namespaces, associated and inverted"
		);
	}

	public function testRcNsFilterMultipleInvert() {
		$this->assertConditions(
			[ # expected
				'rc_namespace NOT IN (1,2,3)',
			],
			[
				'namespace' => '1;2;3',
				'invert' => 1,
			],
			"rc conditions with multiple namespaces inverted"
		);
	}

	public function testRcNsFilterAllContents() {
		$namespaces = $this->getServiceContainer()->getNamespaceInfo()->getSubjectNamespaces();
		$this->assertConditions(
			[ # expected
				'rc_namespace IN (' . $this->getDb()->makeList( $namespaces ) . ')',
			],
			[
				'namespace' => 'all-contents',
			],
			"rc conditions with all-contents"
		);
	}

	public function testRcNsFilterInvalid() {
		$this->assertConditions(
			[ # expected
			],
			[
				'namespace' => 'invalid',
			],
			"rc conditions with invalid namespace"
		);
	}

	public function testRcNsFilterPartialInvalid() {
		$namespaces = array_merge(
			[ 1 ],
			$this->getServiceContainer()->getNamespaceInfo()->getSubjectNamespaces()
		);
		sort( $namespaces );
		$this->assertConditions(
			[ # expected
				'rc_namespace IN (' . $this->getDb()->makeList( $namespaces ) . ')',
			],
			[
				'namespace' => 'all-contents;1;invalid',
			],
			"rc conditions with invalid namespace"
		);
	}

	public function testRcHidemyselfFilter() {
		$user = $this->getTestUser()->getUser();
		$this->assertConditions(
			[ # expected
				$this->getDb()->expr( 'actor_name', '!=', $user->getName() ),
			],
			[
				'hidemyself' => 1,
			],
			"rc conditions: hidemyself=1 (logged in)",
			$user
		);

		$user = User::newFromName( '10.11.12.13', false );
		$this->assertConditions(
			[ # expected
				"actor_name != '10.11.12.13'",
			],
			[
				'hidemyself' => 1,
			],
			"rc conditions: hidemyself=1 (anon)",
			$user
		);
	}

	public function testRcHidebyothersFilter() {
		$user = $this->getTestUser()->getUser();
		$this->assertConditions(
			[ # expected
				'actor_user' => $user->getId(),
			],
			[
				'hidebyothers' => 1,
			],
			"rc conditions: hidebyothers=1 (logged in)",
			$user
		);

		$user = User::newFromName( '10.11.12.13', false );
		$this->assertConditions(
			[ # expected
				'actor_name' => '10.11.12.13',
			],
			[
				'hidebyothers' => 1,
			],
			"rc conditions: hidebyothers=1 (anon)",
			$user
		);
	}

	public function testRcHidepageedits() {
		$this->assertConditions(
			[ # expected
				"rc_type != 0",
			],
			[
				'hidepageedits' => 1,
			],
			"rc conditions: hidepageedits=1"
		);
	}

	public function testRcHidenewpages() {
		$this->assertConditions(
			[ # expected
				"rc_type != 1",
			],
			[
				'hidenewpages' => 1,
			],
			"rc conditions: hidenewpages=1"
		);
	}

	public function testRcHidelog() {
		$this->assertConditions(
			[ # expected
				"rc_type != 3",
			],
			[
				'hidelog' => 1,
			],
			"rc conditions: hidelog=1"
		);
	}

	public function testRcHidehumans() {
		$this->assertConditions(
			[ # expected
				'rc_bot' => 1,
			],
			[
				'hidebots' => 0,
				'hidehumans' => 1,
			],
			"rc conditions: hidebots=0 hidehumans=1"
		);
	}

	public function testRcHidepatrolledDisabledFilter() {
		$this->overrideConfigValue( MainConfigNames::UseRCPatrol, false );
		$this->changesListSpecialPage->filterGroups = [];
		$user = $this->getTestUser()->getUser();
		$this->assertConditions(
			[ # expected
			],
			[
				'hidepatrolled' => 1,
			],
			"rc conditions: hidepatrolled=1 (user not allowed)",
			$user
		);
	}

	public function testRcHideunpatrolledDisabledFilter() {
		$this->overrideConfigValue( MainConfigNames::UseRCPatrol, false );
		$this->changesListSpecialPage->filterGroups = [];
		$user = $this->getTestUser()->getUser();
		$this->assertConditions(
			[ # expected
			],
			[
				'hideunpatrolled' => 1,
			],
			"rc conditions: hideunpatrolled=1 (user not allowed)",
			$user
		);
	}

	public function testRcHidepatrolledFilter() {
		$user = $this->getTestSysop()->getUser();
		$this->assertConditions(
			[ # expected
				'rc_patrolled' => 0,
			],
			[
				'hidepatrolled' => 1,
			],
			"rc conditions: hidepatrolled=1",
			$user
		);
	}

	public function testRcHideunpatrolledFilter() {
		$user = $this->getTestSysop()->getUser();
		$this->assertConditions(
			[ # expected
				'rc_patrolled' => [ 1, 2 ],
			],
			[
				'hideunpatrolled' => 1,
			],
			"rc conditions: hideunpatrolled=1",
			$user
		);
	}

	public function testRcReviewStatusFilter() {
		$user = $this->getTestSysop()->getUser();
		$this->assertConditions(
			[ # expected
				'rc_patrolled' => 1,
			],
			[
				'reviewStatus' => 'manual'
			],
			"rc conditions: reviewStatus=manual",
			$user
		);
		$this->assertConditions(
			[ # expected
				'rc_patrolled' => [ 0, 2 ],
			],
			[
				'reviewStatus' => 'unpatrolled;auto'
			],
			"rc conditions: reviewStatus=unpatrolled;auto",
			$user
		);
	}

	public function testRcHideminorFilter() {
		$this->assertConditions(
			[ # expected
				'rc_minor = 0',
			],
			[
				'hideminor' => 1,
			],
			"rc conditions: hideminor=1"
		);
	}

	public function testRcHidemajorFilter() {
		$this->assertConditions(
			[ # expected
				'rc_minor = 1',
			],
			[
				'hidemajor' => 1,
			],
			"rc conditions: hidemajor=1"
		);
	}

	public function testHideCategorization() {
		$this->assertConditions(
			[
				# expected
				"rc_type != 6"
			],
			[
				'hidecategorization' => 1
			],
			"rc conditions: hidecategorization=1"
		);
	}

	/** @see TempUserTestTrait::enableAutoCreateTempUser */
	protected function enableAutoCreateTempUser( array $configOverrides = [] ): void {
		$this->_enableAutoCreateTempUser( $configOverrides );
		$this->changesListSpecialPage->setTempUserConfig( $this->getServiceContainer()->getTempUserConfig() );
	}

	/** @see TempUserTestTrait::disableAutoCreateTempUser */
	protected function disableAutoCreateTempUser( array $configOverrides = [] ): void {
		$this->_disableAutoCreateTempUser( $configOverrides );
		$this->changesListSpecialPage->setTempUserConfig( $this->getServiceContainer()->getTempUserConfig() );
	}

	public function testRegistrationHideliu() {
		$this->enableAutoCreateTempUser();
		$tempUserMatchPattern = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::LIKE )
			->toSql( $this->getDb() );
		$this->assertConditions(
			[
				"((actor_user IS NULL OR $tempUserMatchPattern))",
			],
			[
				'hideliu' => 1,
			],
			"rc conditions: hideliu=1"
		);
	}

	public function testRegistrationHideanons() {
		$this->enableAutoCreateTempUser();
		$tempUserMatchPattern = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::NOT_LIKE )
			->toSql( $this->getDb() );
		$this->assertConditions(
			[
				"((actor_user IS NOT NULL AND $tempUserMatchPattern))",
			],
			[
				'hideanons' => 1,
			],
			"rc conditions: hideanons=1"
		);
	}

	public function testFilterUserExpLevelAll() {
		$this->assertConditions(
			[
				# expected
			],
			[
				'userExpLevel' => 'registered;unregistered;newcomer;learner;experienced',
			],
			"rc conditions: userExpLevel=registered;unregistered;newcomer;learner;experienced"
		);
	}

	public function testFilterUserExpLevelRegisteredUnregistered() {
		$this->assertConditions(
			[
				# expected
			],
			[
				'userExpLevel' => 'registered;unregistered',
			],
			"rc conditions: userExpLevel=registered;unregistered"
		);
	}

	public function testFilterUserExpLevelRegisteredUnregisteredLearner() {
		$this->assertConditions(
			[
				# expected
			],
			[
				'userExpLevel' => 'registered;unregistered;learner',
			],
			"rc conditions: userExpLevel=registered;unregistered;learner"
		);
	}

	public function testFilterUserExpLevelAllExperienceLevels() {
		$this->disableAutoCreateTempUser();
		$this->assertConditions(
			[
				# expected
				'(actor_user IS NOT NULL)',
			],
			[
				'userExpLevel' => 'newcomer;learner;experienced',
			],
			"rc conditions: userExpLevel=newcomer;learner;experienced"
		);
	}

	public function testFilterUserExpLevelRegistered() {
		$this->disableAutoCreateTempUser();
		$this->assertConditions(
			[
				# expected
				'(actor_user IS NOT NULL)',
			],
			[
				'userExpLevel' => 'registered',
			],
			"rc conditions: userExpLevel=registered"
		);
	}

	public function testFilterUserExpLevelRegisteredTempAccountsEnabled() {
		$this->enableAutoCreateTempUser();
		$tempUserMatchPattern = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::NOT_LIKE )
			->toSql( $this->getDb() );
		$this->assertConditions(
			[
				# expected
				"((actor_user IS NOT NULL AND $tempUserMatchPattern))",
			],
			[
				'userExpLevel' => 'registered',
			],
			"rc conditions: userExpLevel=registered"
		);
	}

	public function testFilterUserExpLevelUnregistered() {
		$this->disableAutoCreateTempUser();
		$this->assertConditions(
			[
				# expected
				'(actor_user IS NULL)'
			],
			[
				'userExpLevel' => 'unregistered',
			],
			"rc conditions: userExpLevel=unregistered"
		);
	}

	public function testFilterUserExpLevelUnregisteredTempAccountsEnabled() {
		$this->enableAutoCreateTempUser();
		$tempUserMatchPattern = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::LIKE )
			->toSql( $this->getDb() );
		$this->assertConditions(
			[
				# expected
				"((actor_user IS NULL OR $tempUserMatchPattern))",
			],
			[
				'userExpLevel' => 'unregistered',
			],
			"rc conditions: userExpLevel=unregistered"
		);
	}

	public function testFilterUserExpLevelRegisteredOrLearner() {
		$this->disableAutoCreateTempUser();
		$this->assertConditions(
			[
				# expected
				'(actor_user IS NOT NULL)',
			],
			[
				'userExpLevel' => 'registered;learner',
			],
			"rc conditions: userExpLevel=registered;learner"
		);
	}

	public function testFilterUserExpLevelLearner() {
		$this->disableAutoCreateTempUser();
		ConvertibleTimestamp::setFakeTime( '20201231000000' );
		$this->assertConditions(
			[
				# expected
				"((actor_user IS NOT NULL AND "
				. "(user_editcount >= 10 AND (user_registration IS NULL OR user_registration <= '{$this->getDb()->timestamp( '20201227000000' )}')) AND "
				. "(user_editcount < 500 OR user_registration > '{$this->getDb()->timestamp( '20201201000000' )}')"
				. "))"
			],
			[
				'userExpLevel' => 'learner'
			],
			"rc conditions: userExpLevel=learner"
		);
	}

	public function testFilterUserExpLevelLearnerWhenTemporaryAccountsEnabled() {
		$this->enableAutoCreateTempUser();
		ConvertibleTimestamp::setFakeTime( '20201231000000' );

		$notLikeTempUserMatchExpression = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::NOT_LIKE )
			->toSql( $this->getDb() );

		$this->assertConditions(
			[
				# expected
				"(((actor_user IS NOT NULL AND $notLikeTempUserMatchExpression) AND "
				. "(user_editcount >= 10 AND (user_registration IS NULL OR user_registration <= '{$this->getDb()->timestamp( '20201227000000' )}')) AND "
				. "(user_editcount < 500 OR user_registration > '{$this->getDb()->timestamp( '20201201000000' )}')"
				. "))"
			],
			[
				'userExpLevel' => 'learner'
			],
			"rc conditions: userExpLevel=learner"
		);
	}

	public function testFilterUserExpLevelUnregisteredOrExperienced() {
		$this->disableAutoCreateTempUser();
		ConvertibleTimestamp::setFakeTime( '20201231000000' );
		$this->assertConditions(
			[
				# expected
				"(actor_user IS NULL OR "
				. "(actor_user IS NOT NULL AND "
					. "(user_editcount >= 500 AND (user_registration IS NULL OR user_registration <= '{$this->getDb()->timestamp( '20201201000000' )}'))"
				. "))"
			],
			[
				'userExpLevel' => 'unregistered;experienced'
			],
			"rc conditions: userExpLevel=unregistered;experienced"
		);
	}

	public function testFilterUserExpLevelUnregisteredOrExperiencedWhenTemporaryAccountsEnabled() {
		$this->enableAutoCreateTempUser();
		ConvertibleTimestamp::setFakeTime( '20201231000000' );

		$notLikeTempUserMatchExpression = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::NOT_LIKE )
			->toSql( $this->getDb() );
		$likeTempUserMatchExpression = $this->getServiceContainer()->getTempUserConfig()
			->getMatchCondition( $this->getDb(), 'actor_name', IExpression::LIKE )
			->toSql( $this->getDb() );

		$this->assertConditions(
			[
				# expected
				"((actor_user IS NULL OR $likeTempUserMatchExpression) OR "
				. "((actor_user IS NOT NULL AND $notLikeTempUserMatchExpression) AND "
					. "(user_editcount >= 500 AND (user_registration IS NULL OR user_registration <= '{$this->getDb()->timestamp( '20201201000000' )}'))"
				. "))"
			],
			[
				'userExpLevel' => 'unregistered;experienced'
			],
			"rc conditions: userExpLevel=unregistered;experienced"
		);
	}

	public function testFilterUserExpLevelRegistrationRequiredToEditRemovesRegistrationFilters() {
		$this->overrideConfigValue(
			MainConfigNames::GroupPermissions,
			[ '*' => [ 'edit' => false ] ]
		);
		parent::setUp();
		$this->assertCount( 3, $this->changesListSpecialPage->filterGroupDefinitions[1]['filters'] );

		$actualFilterGroupDefinitions = [];
		foreach ( $this->changesListSpecialPage->filterGroupDefinitions[1]['filters'] as $key => $value ) {
			if ( $value['name'] ) {
				array_push( $actualFilterGroupDefinitions, $value['name'] );
			}
		}
		$this->assertSame( [ "newcomer", "learner", "experienced" ], $actualFilterGroupDefinitions );
	}

	public function testFilterUserExpLevelRegistrationNotRequiredToEditDoesNotRemoveRegistrationFilters() {
		$this->assertCount( 5, $this->changesListSpecialPage->filterGroupDefinitions[1]['filters'] );

		$actualFilterGroupDefinitions = [];
		foreach ( $this->changesListSpecialPage->filterGroupDefinitions[1]['filters'] as $key => $value ) {
			if ( $value['name'] ) {
				array_push( $actualFilterGroupDefinitions, $value['name'] );
			}
		}
		$this->assertSame( [ "unregistered", "registered", "newcomer", "learner", "experienced" ], $actualFilterGroupDefinitions );
	}

	public function testFilterUserExpLevel() {
		$now = time();
		$this->overrideConfigValues( [
			MainConfigNames::LearnerEdits => 10,
			MainConfigNames::LearnerMemberSince => 4,
			MainConfigNames::ExperiencedUserEdits => 500,
			MainConfigNames::ExperiencedUserMemberSince => 30,
		] );

		$this->createUsers( [
			'Newcomer1' => [ 'edits' => 2, 'days' => 2 ],
			'Newcomer2' => [ 'edits' => 12, 'days' => 3 ],
			'Newcomer3' => [ 'edits' => 8, 'days' => 5 ],
			'Learner1' => [ 'edits' => 15, 'days' => 10 ],
			'Learner2' => [ 'edits' => 450, 'days' => 20 ],
			'Learner3' => [ 'edits' => 460, 'days' => 33 ],
			'Learner4' => [ 'edits' => 525, 'days' => 28 ],
			'Experienced1' => [ 'edits' => 538, 'days' => 33 ],
		], $now );

		// newcomers only
		$this->assertArrayEquals(
			[ 'Newcomer1', 'Newcomer2', 'Newcomer3' ],
			$this->fetchUsers( [ 'newcomer' ], $now )
		);

		// newcomers and learner
		$this->assertArrayEquals(
			[
				'Newcomer1', 'Newcomer2', 'Newcomer3',
				'Learner1', 'Learner2', 'Learner3', 'Learner4',
			],
			$this->fetchUsers( [ 'newcomer', 'learner' ], $now )
		);

		// newcomers and more learner
		$this->assertArrayEquals(
			[
				'Newcomer1', 'Newcomer2', 'Newcomer3',
				'Experienced1',
			],
			$this->fetchUsers( [ 'newcomer', 'experienced' ], $now )
		);

		// learner only
		$this->assertArrayEquals(
			[ 'Learner1', 'Learner2', 'Learner3', 'Learner4' ],
			$this->fetchUsers( [ 'learner' ], $now )
		);

		// more experienced only
		$this->assertArrayEquals(
			[ 'Experienced1' ],
			$this->fetchUsers( [ 'experienced' ], $now )
		);

		// learner and more experienced
		$this->assertArrayEquals(
			[
				'Learner1', 'Learner2', 'Learner3', 'Learner4',
				'Experienced1',
			],
			$this->fetchUsers( [ 'learner', 'experienced' ], $now )
		);
	}

	private function createUsers( array $specs, int $now ) {
		$dbw = $this->getDb();
		foreach ( $specs as $name => $spec ) {
			User::createNew(
				$name,
				[
					'editcount' => $spec['edits'],
					'registration' => $dbw->timestamp( $this->daysAgo( $spec['days'], $now ) ),
					'email' => 'ut',
				]
			);
		}
	}

	private function fetchUsers( array $filters, int $now ): array {
		$tables = [];
		$conds = [];
		$fields = [];
		$query_options = [];
		$join_conds = [];

		sort( $filters );

		call_user_func_array(
			[ $this->changesListSpecialPage, 'filterOnUserExperienceLevel' ],
			[
				get_class( $this->changesListSpecialPage ),
				$this->changesListSpecialPage->getContext(),
				$this->changesListSpecialPage->getDB(),
				&$tables,
				&$fields,
				&$conds,
				&$query_options,
				&$join_conds,
				$filters,
				$now
			]
		);

		// @todo: This is not at all safe or sensible. It just blindly assumes
		// nothing in $conds depends on any other tables.
		$result = $this->getDb()->newSelectQueryBuilder()
			->select( 'user_name' )
			->from( 'user' )
			->leftJoin( 'actor', null, 'actor_user=user_id' )
			->where( $conds )
			->andWhere( [ 'user_email' => 'ut' ] )
			->fetchResultSet();

		$usernames = [];
		foreach ( $result as $row ) {
			$usernames[] = $row->user_name;
		}

		return $usernames;
	}

	private function daysAgo( int $days, int $now ): int {
		$secondsPerDay = 86400;
		return $now - $days * $secondsPerDay;
	}

	public function testGetStructuredFilterJsData() {
		$this->changesListSpecialPage->filterGroups = [];

		$definition = [
			[
				'name' => 'gub-group',
				'title' => 'gub-group-title',
				'class' => ChangesListBooleanFilterGroup::class,
				'filters' => [
					[
						'name' => 'hidefoo',
						'label' => 'foo-label',
						'description' => 'foo-description',
						'default' => true,
						'showHide' => 'showhidefoo',
						'priority' => 2,
					],
					[
						'name' => 'hidebar',
						'label' => 'bar-label',
						'description' => 'bar-description',
						'default' => false,
						'priority' => 4,
					]
				],
			],

			[
				'name' => 'des-group',
				'title' => 'des-group-title',
				'class' => ChangesListStringOptionsFilterGroup::class,
				'isFullCoverage' => true,
				'filters' => [
					[
						'name' => 'grault',
						'label' => 'grault-label',
						'description' => 'grault-description',
					],
					[
						'name' => 'garply',
						'label' => 'garply-label',
						'description' => 'garply-description',
					],
				],
				'queryCallable' => static function () {
				},
				'default' => ChangesListStringOptionsFilterGroup::NONE,
			],

			[
				'name' => 'unstructured',
				'class' => ChangesListBooleanFilterGroup::class,
				'filters' => [
					[
						'name' => 'hidethud',
						'showHide' => 'showhidethud',
						'default' => true,
					],

					[
						'name' => 'hidemos',
						'showHide' => 'showhidemos',
						'default' => false,
					],
				],
			],

		];

		$this->changesListSpecialPage->registerFiltersFromDefinitions( $definition );

		$this->assertArrayEquals(
			[
				// Filters that only display in the unstructured UI are
				// are not included, and neither are groups that would
				// be empty due to the above.
				'groups' => [
					[
						'name' => 'gub-group',
						'title' => 'gub-group-title',
						'type' => ChangesListBooleanFilterGroup::TYPE,
						'priority' => -1,
						'filters' => [
							[
								'name' => 'hidebar',
								'label' => 'bar-label',
								'description' => 'bar-description',
								'default' => false,
								'priority' => 4,
								'cssClass' => null,
								'conflicts' => [],
								'subset' => [],
								'defaultHighlightColor' => null
							],
							[
								'name' => 'hidefoo',
								'label' => 'foo-label',
								'description' => 'foo-description',
								'default' => true,
								'priority' => 2,
								'cssClass' => null,
								'conflicts' => [],
								'subset' => [],
								'defaultHighlightColor' => null
							],
						],
						'fullCoverage' => true,
						'conflicts' => [],
					],

					[
						'name' => 'des-group',
						'title' => 'des-group-title',
						'type' => ChangesListStringOptionsFilterGroup::TYPE,
						'priority' => -2,
						'fullCoverage' => true,
						'filters' => [
							[
								'name' => 'grault',
								'label' => 'grault-label',
								'description' => 'grault-description',
								'cssClass' => null,
								'priority' => -2,
								'conflicts' => [],
								'subset' => [],
								'defaultHighlightColor' => null
							],
							[
								'name' => 'garply',
								'label' => 'garply-label',
								'description' => 'garply-description',
								'cssClass' => null,
								'priority' => -3,
								'conflicts' => [],
								'subset' => [],
								'defaultHighlightColor' => null
							],
						],
						'conflicts' => [],
						'separator' => ';',
						'default' => ChangesListStringOptionsFilterGroup::NONE,
					],
				],
				'messageKeys' => [
					'gub-group-title',
					'bar-label',
					'bar-description',
					'foo-label',
					'foo-description',
					'des-group-title',
					'grault-label',
					'grault-description',
					'garply-label',
					'garply-description',
				],
			],
			$this->changesListSpecialPage->getStructuredFilterJsData(),
			/** ordered= */ false,
			/** named= */ true
		);
	}

	public static function provideParseParameters() {
		return [
			[ 'hidebots', [ 'hidebots' => true ] ],

			[ 'bots', [ 'hidebots' => false ] ],

			[ 'hideminor', [ 'hideminor' => true ] ],

			[ 'minor', [ 'hideminor' => false ] ],

			[ 'hidemajor', [ 'hidemajor' => true ] ],

			[ 'hideliu', [ 'hideliu' => true ] ],

			[ 'hidepatrolled', [ 'hidepatrolled' => true ] ],

			[ 'hideunpatrolled', [ 'hideunpatrolled' => true ] ],

			[ 'hideanons', [ 'hideanons' => true ] ],

			[ 'hidemyself', [ 'hidemyself' => true ] ],

			[ 'hidebyothers', [ 'hidebyothers' => true ] ],

			[ 'hidehumans', [ 'hidehumans' => true ] ],

			[ 'hidepageedits', [ 'hidepageedits' => true ] ],

			[ 'pagedits', [ 'hidepageedits' => false ] ],

			[ 'hidenewpages', [ 'hidenewpages' => true ] ],

			[ 'hidecategorization', [ 'hidecategorization' => true ] ],

			[ 'hidelog', [ 'hidelog' => true ] ],

			[
				'userExpLevel=learner;experienced',
				[
					'userExpLevel' => 'learner;experienced'
				],
			],

			// A few random combos
			[
				'bots,hideliu,hidemyself',
				[
					'hidebots' => false,
					'hideliu' => true,
					'hidemyself' => true,
				],
			],

			[
				'minor,hideanons,categorization',
				[
					'hideminor' => false,
					'hideanons' => true,
					'hidecategorization' => false,
				]
			],

			[
				'hidehumans,bots,hidecategorization',
				[
					'hidehumans' => true,
					'hidebots' => false,
					'hidecategorization' => true,
				],
			],

			[
				'hidemyself,userExpLevel=newcomer;learner,hideminor',
				[
					'hidemyself' => true,
					'hideminor' => true,
					'userExpLevel' => 'newcomer;learner',
				],
			],
		];
	}

	public static function provideGetFilterConflicts() {
		return [
			[
				"parameters" => [],
				"expectedConflicts" => false,
			],
			[
				"parameters" => [
					"hideliu" => true,
					"userExpLevel" => "newcomer",
				],
				"expectedConflicts" => false,
			],
			[
				"parameters" => [
					"hideanons" => true,
					"userExpLevel" => "learner",
				],
				"expectedConflicts" => false,
			],
			[
				"parameters" => [
					"hidemajor" => true,
					"hidenewpages" => true,
					"hidepageedits" => true,
					"hidecategorization" => false,
					"hidelog" => true,
					"hideWikidata" => true,
				],
				"expectedConflicts" => true,
			],
			[
				"parameters" => [
					"hidemajor" => true,
					"hidenewpages" => false,
					"hidepageedits" => true,
					"hidecategorization" => false,
					"hidelog" => false,
					"hideWikidata" => true,
				],
				"expectedConflicts" => true,
			],
			[
				"parameters" => [
					"hidemajor" => true,
					"hidenewpages" => false,
					"hidepageedits" => false,
					"hidecategorization" => true,
					"hidelog" => true,
					"hideWikidata" => true,
				],
				"expectedConflicts" => false,
			],
			[
				"parameters" => [
					"hideminor" => true,
					"hidenewpages" => true,
					"hidepageedits" => true,
					"hidecategorization" => false,
					"hidelog" => true,
					"hideWikidata" => true,
				],
				"expectedConflicts" => false,
			],
		];
	}

	/**
	 * @dataProvider provideGetFilterConflicts
	 */
	public function testGetFilterConflicts( $parameters, $expectedConflicts ) {
		$context = new RequestContext;
		$context->setRequest( new FauxRequest( $parameters ) );
		$this->changesListSpecialPage->setContext( $context );

		$this->assertEquals(
			$expectedConflicts,
			$this->changesListSpecialPage->areFiltersInConflict()
		);
	}

	public static function validateOptionsProvider() {
		return [
			[
				[ 'hideanons' => 1, 'hideliu' => 1, 'hidebots' => 1 ],
				true,
				[ 'userExpLevel' => 'unregistered', 'hidebots' => 1, ],
				true,
			],
			[
				[ 'hideanons' => 1, 'hideliu' => 1, 'hidebots' => 0 ],
				true,
				[ 'hidebots' => 0, 'hidehumans' => 1 ],
				true,
			],
			[
				[ 'hideanons' => 1 ],
				true,
				[ 'userExpLevel' => 'registered' ],
				true,
			],
			[
				[ 'hideliu' => 1 ],
				true,
				[ 'userExpLevel' => 'unregistered' ],
				true,
			],
			[
				[ 'hideanons' => 1, 'hidebots' => 1 ],
				true,
				[ 'userExpLevel' => 'registered', 'hidebots' => 1 ],
				true,
			],
			[
				[ 'hideliu' => 1, 'hidebots' => 0 ],
				true,
				[ 'userExpLevel' => 'unregistered', 'hidebots' => 0 ],
				true,
			],
			[
				[ 'hidemyself' => 1, 'hidebyothers' => 1 ],
				true,
				[],
				true,
			],
			[
				[ 'hidebots' => 1, 'hidehumans' => 1 ],
				true,
				[],
				true,
			],
			[
				[ 'hidepatrolled' => 1, 'hideunpatrolled' => 1 ],
				true,
				[],
				true,
			],
			[
				[ 'hideminor' => 1, 'hidemajor' => 1 ],
				true,
				[],
				true,
			],
			[
				// changeType
				[ 'hidepageedits' => 1, 'hidenewpages' => 1, 'hidecategorization' => 1, 'hidelog' => 1, 'hidenewuserlog' => 1 ],
				true,
				[],
				true,
			],
		];
	}
}
