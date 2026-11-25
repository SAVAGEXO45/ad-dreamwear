<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSite;
use App\Models\SiteSetting;
use App\Services\CatalogueMedia;
use Illuminate\Support\Facades\DB;
use Throwable;

class SiteController extends Controller
{
    public function __construct(private CatalogueMedia $media) {}

    public function edit()
    {
        return view('admin.website', ['site' => SiteSetting::current()]);
    }

    public function update(SaveSite $request)
    {
        $banners = ['banner_desktop', 'banner_mobile'];
        $fields = $request->safe()->except([...$banners, 'remove_banner_desktop', 'remove_banner_mobile']);
        $stored = [];

        try {
            foreach ($banners as $banner) {
                if ($request->hasFile($banner)) {
                    $stored[$banner] = $this->media->store($request->file($banner), $banner);
                }
            }

            $retired = DB::transaction(function () use ($request, $banners, $fields, $stored) {
                $site = SiteSetting::whereKey(1)->lockForUpdate()->firstOrFail();
                $content = array_replace(config('catalogue.defaults'), $site->content, $fields);
                $retired = [];
                foreach ($banners as $banner) {
                    if (isset($stored[$banner]) || $request->boolean('remove_'.$banner)) {
                        $retired[] = $content[$banner];
                        $content[$banner] = $stored[$banner] ?? null;
                    }
                }
                $site->update(['content' => $content]);

                return $retired;
            });
        } catch (Throwable $error) {
            $this->media->discard($stored);
            throw $error;
        }

        $this->media->discard($retired);

        return back()->with('status', 'Website changes published.');
    }
}
