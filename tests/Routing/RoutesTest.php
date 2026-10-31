<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RoutesTest extends TestCase
{
    public function testReceiptRouteOnlyAcceptsFullTokens(): void
    {
        $routes = (new YamlFileLoader(new FileLocator(__DIR__ . '/../../config')))->load('routes.yaml');
        $matcher = new UrlMatcher($routes, new RequestContext());

        $token = str_repeat('a', 64);
        $this->assertSame($token, $matcher->match('/_withdrawal/receipt/' . $token)['token']);

        $this->expectException(ResourceNotFoundException::class);
        $matcher->match('/_withdrawal/receipt/short');
    }
}
