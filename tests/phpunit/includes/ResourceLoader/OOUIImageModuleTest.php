<?php

namespace MediaWiki\Tests\ResourceLoader;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\HookContainer\StaticHookRegistry;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\OOUIImageModule;
use MediaWiki\Skin\SkinFactory;
use MediaWiki\Tests\Unit\DummyServicesTrait;

/**
 * @group ResourceLoader
 * @covers \MediaWiki\ResourceLoader\OOUIImageModule
 */
class OOUIImageModuleTest extends ResourceLoaderTestCase {
	use DummyServicesTrait;

	public function testNonDefaultSkin() {
		$module = new OOUIImageModule( [
			'class' => OOUIImageModule::class,
			'name' => 'icons',
			'rootPath' => 'tests/phpunit/data/resourceloader/oouiimagemodule',
		] );
		$module->setHookContainer( new HookContainer(
			new StaticHookRegistry(),
			$this->getServiceContainer()->getObjectFactory()
		) );

		// Pretend that 'fakemonobook' is a real skin using the Apex theme
		$skinFactory = new SkinFactory( $this->getDummyObjectFactory(), [] );
		$skinFactory->register(
			'fakemonobook',
			'FakeMonoBook',
			[]
		);
		$this->setService( 'SkinFactory', $skinFactory );

		$reset = ExtensionRegistry::getInstance()->setAttributeForTest(
			'SkinOOUIThemes', [ 'fakemonobook' => 'Apex' ]
		);

		$styles = $module->getStyles( $this->getResourceLoaderContext( [ 'skin' => 'fakemonobook' ] ) );
		$this->assertMatchesRegularExpression(
			'/stu-apex/',
			$styles['all'],
			'Generated styles use the non-default image'
		);

		$styles = $module->getStyles( $this->getResourceLoaderContext() );
		$this->assertMatchesRegularExpression(
			'/stu-wikimediaui/',
			$styles['all'],
			'Generated styles use the default image'
		);
	}

}
