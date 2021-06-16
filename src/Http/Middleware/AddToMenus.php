<?php
namespace ProcessMaker\Package\Adoa\Http\Middleware;

use Closure;
use Lavary\Menu\Facade as Menu;
use ProcessMaker\Package\Adoa\Models\AdoaUsers;


class AddToMenus
{
    public function handle($request, Closure $next)
    {
        $menu = Menu::get('topnav');
        // $menu->add(__('Print'), ['route' => 'package.adoa.tab.report']);
        // $menu->add(__('Print RWA'), ['route' => 'package.adoa.tab.rwa-report']);

        return $next($request);
    }

}
