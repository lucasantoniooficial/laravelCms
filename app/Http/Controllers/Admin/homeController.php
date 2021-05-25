<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Visitor;
use App\Page;
use App\User;

class homeController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function index(Request $request){

        $visitsCount = 0;
        $onlineCount = 0;
        $pageCount = 0;
        $userCount = 0;

        $interval = intval($request->input('interval', 30));
        if($interval > 120){
            $interval = 120;
        }

        //Contagem de visitantes
        $dateInterval = date('Y-m-d H:i:s', strtotime('-'.$interval.' days'));
        
        $visitsCount = Visitor::where('date_access', '>=', $dateInterval)->count();


        //Contagem de usuários online
        $dateLimit = date('Y-m-d H:i:s', strtotime('-5 minutes'));
       
        $onlineList = Visitor::select('ip')->where('date_access', '>=', $dateLimit)
        ->groupBy('ip')
        ->get();
        $onlineCount = count($onlineList);

        //Contagem de páginas
        $pageCount = Page::count();

        //Contagem de usuários
        $userCount = User::count();

        //Organizando os dados do gráfico
        $pagePie = [];
        $visitsAll = Visitor::selectRaw('page, count(page) as c')
        ->where('date_access', '>=', $dateInterval)
        ->groupBy('page')
        ->get();

        foreach($visitsAll as $visit){
            $pagePie[$visit['page'] ] = intval($visit['c']);
        }

        

        $pageLabels = json_encode(array_keys($pagePie));
        $pageValues = json_encode(array_values($pagePie));

        return view('Admin.home',[
            'visitsCount' => $visitsCount,
            'onlineCount' => $onlineCount,
            'userCount' => $userCount,
            'pageCount' => $pageCount,
            'pageLabels' =>$pageLabels,
            'pageValues' => $pageValues,
            'dateInterval' => $interval
        ]);
    }
}
