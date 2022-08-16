<?php

namespace Outl1ne\PageManager\Traits;

use Illuminate\Support\Arr;

trait DataReplaceHelpers
{
    protected function collectValues($data, array $keys)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[] = Arr::pluck($data, $key);
        }
        $values = Arr::flatten($values);
        $values = array_filter($values);
        $values = array_unique($values);
        return array_values($values);
    }

    protected function replaceValues(&$data, $replacementValueMap, array $replacementKeys)
    {
        foreach ($data as &$localeData) {
            $this->walkAndReplace($localeData, $replacementValueMap, $replacementKeys);
        }
        return $data;
    }

    private function walkAndReplace(&$data, $replacementValueMap, $replacementKeys, $currentKey = null, $currentFullKey = null)
    {
        foreach ($data as $key => &$value) {
            $newFullKey = $currentFullKey ? "$currentFullKey.$key" : $key;

            if (is_iterable($value)) {
                $this->walkAndReplace($value, $replacementValueMap, $replacementKeys, $key, $newFullKey);
            } else {
                foreach ($replacementKeys as $rplKey) {
                    $rgxKey = str_replace('.', '\.', $rplKey);
                    $rgxKey = str_replace('*', '\d', $rgxKey);
                    $rgxKey = "($rgxKey)";

                    if (preg_match($rgxKey, $newFullKey)) {
                        $lastKey = Arr::last(explode('.', $key));
                        $data[$lastKey] = $replacementValueMap[$newFullKey][$data[$lastKey]] ?? null;
                    }
                }
            }
        }
    }
}
