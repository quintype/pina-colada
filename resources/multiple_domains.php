<?php

function getQuintypeAPIHost($currentHost) {
    if(config("quintype.api-host-remove-web") && strpos($currentHost, '-web.') !== false) {
        return "https://" . str_replace("-web", "", $currentHost);
    }

    if(config("quintype.host-to-api-host")) {
        $hosts = config("quintype.host-to-api-host");
        if(isset($hosts[$currentHost]))
            return $hosts[$currentHost];
    }

    return config('quintype.api-host');
}
