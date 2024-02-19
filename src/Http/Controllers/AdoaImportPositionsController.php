<?php

namespace ProcessMaker\Package\Adoa\Http\Controllers;
use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\Adoa\Jobs\ImportPositions;
use RBAC;
use Illuminate\Http\Request;
use URL;

class AdoaImportPositionsController extends Controller
{
    public function importPositionsProd()
    {
        ImportPositions::dispatch('https://hrsieapi.azdoa.gov/api/hrorg/position.csv?_sort=POSITION&_size=max');
        return response(['status' => true], 201);
    }

    public function importPositionsDev()
    {
        ImportPositions::dispatch('https://hrsieapitest.azdoa.gov/api/hrorg/position.csv?_sort=POSITION&_size=max');
        return response(['status' => true], 201);
    }
}
