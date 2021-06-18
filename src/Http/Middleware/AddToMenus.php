<?php
namespace ProcessMaker\Package\Adoa\Http\Middleware;

use Auth;
use Closure;
use Lavary\Menu\Builder;
use Lavary\Menu\Facade as Menu;

class AddToMenus
{
    const ADMIN_GROUP_ID = 3;
    
    const AGENCY_GROUP_ID = 8;
    
    private $inAdminGroup = false;
    
    private $inAgencyGroup = false;
    
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $this->setGroupStatus();
            
            $requestMenu = Menu::get('sidebar_request');
            $taskMenu = Menu::get('sidebar_task');
            
            if (! $this->inAdminGroup && ! $this->inAgencyGroup) {
                $this->clearMenu($requestMenu);
                $this->clearMenu($taskMenu);
            }
            
            $this->addToMenu($requestMenu);
            $this->addToMenu($taskMenu);
        }

        return $next($request);
    }

    private function setGroupStatus()
    {
        $groups = Auth::user()->groups->pluck('id');
        $this->inAdminGroup = $groups->contains(self::ADMIN_GROUP_ID);
        $this->inAgencyGroup = $groups->contains(self::AGENCY_GROUP_ID);
    }
    
    private function clearMenu(Builder $menu)
    {
        $submenu = $menu->first();
        
        foreach($submenu->children() as $key => $item) {
            $menu->items->forget($key + 1);
        }
    }
    
    private function addToMenu(Builder $menu)
    {
        $submenu = $menu->first();
            
        $submenu->add(__('My Requests'), [
            'route' => ['package.adoa.listRequests'],
            'icon' => 'fa-tasks',
        ]);
        
        $submenu->add(__('Shared With Me'), [
            'route' => ['package.adoa.sharedWithMe'],
            'icon' => 'fa-share-square',
        ]);
        
        if ($this->inAgencyGroup) {
            $submenu->add(__('Agency Requests'), [
                'route' => ['package.adoa.agencyRequests', 'groupId' => self::AGENCY_GROUP_ID],
                'icon' => 'fa-laptop-house',
            ]);
        }
    }
}
