<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Twig;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Twig\Loader\TemplateLocator;
use Contao\CoreBundle\Twig\Loader\ThemeNamespace;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class TemplateDiscoveryTest extends TestCase
{
    /**
     * Without the namespace root marker, Contao drops the directory from the
     * identifier and cannot find the templates of the fragment controllers.
     */
    public function testContaoFindsFragmentTemplatesWithTheirDirectory(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchFirstColumn')->willReturn([]);

        $locator = new TemplateLocator(sys_get_temp_dir(), new ResourceFinder([]), new ThemeNamespace(), $connection);
        $templates = $locator->findTemplates(\dirname(__DIR__, 2) . '/contao/templates');

        $this->assertArrayHasKey('content_element/withdrawal_form.html.twig', $templates);
        $this->assertArrayHasKey('frontend_module/withdrawal_link.html.twig', $templates);
    }
}
