<?php

namespace Outl1ne\PageManager\Nova\Fields;

use Outl1ne\PageManager\NPM;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Http\Requests\NovaRequest;

class PrefixSlugField extends Slug
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'prefix-slug-field';

    public function pathPrefix($path = [])
    {
        return $this->withMeta([
            'pathPrefix' => $path,
        ]);
    }

    public function pathSuffix($path)
    {
        return $this->withMeta([
            'pathSuffix' => !empty($path) ? '/' . $path : null,
        ]);
    }

    public function fill(NovaRequest $request, $model)
    {
        $attribute = $this->meta['translatable']['original_attribute'] ?? $this->attribute;

        if (isset($this->fillCallback)) {
            return call_user_func($this->fillCallback, $request, $model, $attribute, $attribute);
        }

        $data = $request->get($attribute);
        $locales = NPM::getLocales();

        $newSlugs = [];
        foreach ($locales as $key => $localeName) {
            $slug = $data[$key] ?? '';
            $slug = trim($slug);

            // Remove all slashes
            $slug = preg_replace('/[\/]+/', '', $slug);
            if (empty($slug)) $slug = '/';

            $newSlugs[$key] = $slug;
        }

        $model->{$attribute} = $newSlugs;
    }

    public function jsonSerialize(): array
    {
        $novaRequest = app(NovaRequest::class);

        $showCustomizeButton = false;

        if ($novaRequest->isUpdateOrUpdateAttachedRequest()) {
            $this->readonly();
            $showCustomizeButton = true;
        }

        return array_merge([
            'updating' => $novaRequest->isUpdateOrUpdateAttachedRequest(),
            'separator' => '-',
            'showCustomizeButton' => $showCustomizeButton,
        ], parent::jsonSerialize());
    }
}
