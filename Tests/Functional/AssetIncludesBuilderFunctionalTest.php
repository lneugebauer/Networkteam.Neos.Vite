<?php

namespace Networkteam\Neos\Vite\Tests\Functional;

use Neos\Flow\Tests\FunctionalTestCase;
use Networkteam\Neos\Vite\AssetIncludesBuilder;

class AssetIncludesBuilderFunctionalTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function developmentInclude()
    {
        $serverConfiguration = [
            '_default' => [
                'url' => 'http://localhost:1234'
            ],
            'my-site' => [
                'url' => 'http://localhost:4321'
            ]
        ];

        $builder = new AssetIncludesBuilder('my-site', 'outputPath', 'manifest');
        $this->inject($builder, 'serverConfiguration', $serverConfiguration);

        $html = $builder->developmentInclude('main.js');
        $this->assertEquals('<script type="module" src="http://localhost:4321/main.js"></script>', $html, 'should use site specific server URL');

        $builder = new AssetIncludesBuilder('another-site', 'outputPath', 'manifest');
        $this->inject($builder, 'serverConfiguration', $serverConfiguration);

        $html = $builder->developmentInclude('main.js');
        $this->assertEquals('<script type="module" src="http://localhost:1234/main.js"></script>', $html, 'should use default server URL');

        $file = $builder->developmentUrl('main.js');
        $this->assertEquals('http://localhost:1234/main.js', $file, 'should use default server URL');
    }

    /**
     * @test
     */
    public function productionIncludes()
    {
        $builder = new AssetIncludesBuilder('my-site', 'resource://Networkteam.Neos.Vite/Public/Dist', '.vite/manifest.json');

        $html = $builder->productionIncludes('main.js');
        $this->assertEquals(
            '<link rel="stylesheet" href="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/main.b82dbe22.css">' . PHP_EOL .
            '<link rel="stylesheet" href="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/shared.a834bfc3.css">' . PHP_EOL .
            '<script type="module" src="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/main.4889e940.js"></script>' . PHP_EOL .
            '<link rel="modulepreload" href="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/shared.83069a53.js">'
            , $html, 'should create correct includes for main entry');

        $file = $builder->productionUrl('main.js');
        $this->assertEquals('http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/main.4889e940.js', $file, 'should create correct URL for main entry');

        $html = $builder->productionIncludes('views/foo.js');
        $this->assertEquals(
            '<link rel="stylesheet" href="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/shared.a834bfc3.css">' . PHP_EOL .
            '<script type="module" src="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/foo.869aea0d.js"></script>' . PHP_EOL .
            '<link rel="modulepreload" href="http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/shared.83069a53.js">'
            , $html, 'should create correct includes for other entries');

        $file = $builder->productionUrl('views/foo.js');
        $this->assertEquals('http://localhost/_Resources/Testing/Static/Packages/Networkteam.Neos.Vite/Dist/assets/foo.869aea0d.js', $file, 'should create correct URL for other entry');
    }
}
