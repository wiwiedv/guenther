<?php

namespace wiwiedv;

use Symfony\Component\HttpFoundation\JsonResponse;

class GuentherResponse extends JsonResponse
{
    protected $guentherHeaders = array(
        "X-Guenther-Version" => "1.0"
    );

    public function __construct($data = array(), $status = 200, $headers = array()) {
        $headers = array_merge($this->guentherHeaders, $headers);
        parent::__construct($data, $status, $headers);
    }
}