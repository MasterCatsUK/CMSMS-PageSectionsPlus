<?php
/** SPDX-License-Identifier: GPL-2.0-or-later. See the module docs/LICENSE file. */
if (!defined('CMS_VERSION')) exit;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$reply = array('success' => false);

if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') !== 'POST') {
    http_response_code(405);
    $reply['error'] = 'The upload request must use POST.';
    echo json_encode($reply);
    exit;
}

if (!get_userid(false)) {
    http_response_code(403);
    $reply['error'] = 'Your CMSMS session has expired. Reload the page and try again.';
    echo json_encode($reply);
    exit;
}

$file = null;
if (isset($_FILES[$id.'file']) && is_array($_FILES[$id.'file'])) {
    $file = $_FILES[$id.'file'];
} else {
    foreach ($_FILES as $candidate) {
        if (is_array($candidate) && !empty($candidate['tmp_name'])) {
            $file = $candidate;
            break;
        }
    }
}

$image_url = '';
if (isset($params['image_url'])) {
    $image_url = $params['image_url'];
} elseif (isset($params[$id.'image_url'])) {
    $image_url = $params[$id.'image_url'];
} elseif (isset($params['psuploadimage_url'])) {
    $image_url = $params['psuploadimage_url'];
}

if ($file && isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_NO_FILE) {
    $result = $this->handleImageUpload($file);
} elseif (is_string($image_url) && trim($image_url) !== '') {
    if (!$this->CanUseImageLink()) {
        http_response_code(403);
        $reply['error'] = 'You do not have permission to import an image link.';
        echo json_encode($reply);
        exit;
    }
    require_once dirname(__FILE__).'/lib/import_image.php';
    $result = psplus_import_image(trim($image_url));
} else {
    $result = array('error' => 'Choose an image file or paste a direct image link.');
}

if (isset($result['error'])) {
    http_response_code(400);
    $reply['error'] = $result['error'];
    echo json_encode($reply);
    exit;
}

$image = isset($result['image']) ? $result['image'] : '';
$reply = array(
    'success' => true,
    'image' => $image,
    'url' => $this->storedImageUrl($image),
);

echo json_encode($reply, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
