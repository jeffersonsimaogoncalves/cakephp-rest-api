<?php
/** @var array $response */
/** @var array $responseFormat */
/** @var array $paging */
/** @var array $pagination */
if (empty($response[$responseFormat['resultKey']])) {
    $response[$responseFormat['resultKey']] = [
        $responseFormat['messageKey'] => $responseFormat['defaultMessageText'],
    ];
}

if (isset($paging)) {
    $response['paging'] = $paging;
}
if (isset($pagination)) {
    $response['paging'] = $pagination;
}

echo json_encode($response);
