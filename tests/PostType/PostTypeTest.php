<?php

namespace Themosis\Tests\PostType;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;
use Themosis\Core\Application;
use Themosis\Hook\ActionBuilder;
use Themosis\Hook\FilterBuilder;
use Themosis\PostType\Contracts\PostTypeInterface;
use Themosis\PostType\Factory;

class PostTypeTest extends TestCase
{
    protected $application;

    protected function getApplication()
    {
        if (! is_null($this->application)) {
            return $this->application;
        }

        $this->application = new Application();

        $this->application->bind('config', function () {
            $config = new Repository();
            $config->set('app.locale', 'en_US');

            return $config;
        });

        return $this->application;
    }

    protected function getFactory()
    {
        $app = $this->getApplication();

        return new Factory(
            $app,
            new ActionBuilder($app),
            new FilterBuilder($app)
        );
    }

    public function testCreatePostTypeWithDefaults()
    {
        $factory = $this->getFactory();
        $postType = $factory->make('book', 'Books', 'Book');

        $this->assertInstanceOf(PostTypeInterface::class, $postType);

        $this->assertNotEmpty($postType->getLabels());
        $this->assertEquals($postType->getLabels(), $postType->getArguments()['labels']);
        $this->assertTrue($postType->getArgument('public'));
        $this->assertTrue($postType->getArgument('show_in_rest'));
        $this->assertEquals(20, $postType->getArgument('menu_position'));
        $this->assertTrue($postType->getArgument('has_archive'));
    }

    public function testCreatePostTypeWithCustomArgs()
    {
        $factory = $this->getFactory();
        $postType = $factory->make('product', 'Products', 'Product')
            ->setArguments([
                'public' => false,
                'menu_position' => 35,
                'has_archive' => false
            ]);

        $this->assertFalse($postType->getArgument('public'));
        $this->assertEquals(35, $postType->getArgument('menu_position'));
        $this->assertFalse($postType->getArgument('has_archive'));

        $postType->setLabels([
            'add_new_item' => 'Add Me'
        ]);

        $this->assertEquals('Add Me', $postType->getLabel('add_new_item'));
        $this->assertEquals('View Product', $postType->getLabel('view_item'));
    }
}
