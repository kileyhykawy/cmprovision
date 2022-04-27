<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Cm;
use App\Models\Project;
use App\Models\Firmware;
use App\Models\Image;
use App\Models\Script;
use App\Models\Label;
use App\Http\Controllers\AddImageController;

/*
 * Put protection around this to only load the helper function once. When running
 * `artisan config:cache`, it tries to load this file multiple times and will
 * return in `cmsError` already declared error is we don't do this.
 */
if (!function_exists('cmsQuery')) {
    function cmsQuery(Request $request, Builder $query) {
        $validatedData = $request->validate([
            'limit' => 'integer|min:0',
            'sortDesc' => 'in:provisioning_started_at'
        ]);

        $limit = $validatedData['limit'] ?? null;
        $sortDesc = $validatedData['sortDesc'] ?? null;

        if ($limit) {
            $query = $query->take($limit);
        }
        if ($sortDesc) {
            $query = $query->orderBy($sortDesc, 'desc');
        }
        $query = $query->orderBy('id');

        return $query;
    }
}

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Routes to list all information of a certain group */

Route::middleware('auth:sanctum')->get('/cms', function (Request $request) {
    return cmsQuery($request, Cm::query())->get();
});

/*
 * This is a beta route as we are not sure it will actually be used.
 */
Route::middleware('auth:sanctum')->get('/cms/by_board/latest', function (Request $request) {
    /*
     * Sort all CMs by most recent started timestamp so that we can pull latest
     * for each board.
     */
    $sub = Cm::orderByDesc('provisioning_started_at');
    return Cm::fromSub($sub, 'cm')
        ->groupBy('provisioning_board')
        ->orderBy('provisioning_board')
        ->get();
});

Route::middleware('auth:sanctum')->get('/projects/{projectId}/cms', function (Request $request, $projectId) {
    return cmsQuery($request, Cm::where('project_id', $projectId))->get();
});

Route::middleware('auth:sanctum')->get('/projects', function (Request $request) {
    return Project::orderBy('name')->get();
});

Route::middleware('auth:sanctum')->get('/images', function (Request $request) {
    return Image::orderBy('filename')->orderBy('id')->get();
});

Route::middleware('auth:sanctum')->get('/firmware', function (Request $request) {
    return Firmware::all();
});

Route::middleware('auth:sanctum')->get('/scripts', function (Request $request) {
    return Script::orderBy('name')->get();
});

Route::middleware('auth:sanctum')->get('/labels', function (Request $request) {
    return Label::orderBy('name')->get();
});

/* Routes to add or update individual objects */

Route::middleware('auth:sanctum')->post('/images', function (Request $request) {
    if ($request->user()->tokenCan('create'))
    {
        $c = new AddImageController;
        return $c->store($request);
    }
    else
    {
        App::abort(403, "API user lacks 'create' permission");
    }
});

Route::middleware('auth:sanctum')->get('/images/{imageId}', function (Request $request, $imageId) {
    return Image::findOrFail($imageId);
});

Route::middleware('auth:sanctum')->delete('/images/{imageId}', function (Request $request, $imageId) {
    if ($request->user()->tokenCan('delete'))
    {
        Image::findOrFail($imageId)->delete();
    }
    else
    {
        App::abort(403, "API user lacks 'delete' permission");
    }
});

Route::middleware('auth:sanctum')->get('/projects/{projectId}', function (Request $request, $projectId) {
    return Project::findOrFail($projectId);
});

Route::middleware('auth:sanctum')->patch('/projects/{projectId}', function (Request $request, $projectId) {
    if ($request->user()->tokenCan('update'))
    {
        $project = Project::findOrFail($projectId);
        $project->update($request->all());
        return $project;
    }
    else
    {
        App::abort(403, "API user lacks 'update' permission");
    }
});
