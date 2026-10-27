<?php
if (isset($_GET['number'])) {
    $input = preg_replace('/[^0-9]/', '', $_GET['number']); // Keep only digits

    if (empty($input)) {
        exit;
    }

    $digits = str_split($input);
    $images = [];
    $total_width = 0;
    $max_height = 0;

    foreach ($digits as $digit) {
        $filename = "tn{$digit}.gif";
        if (!file_exists($filename)) {
            exit;
        }

        $img = imagecreatefromgif($filename);
        if (!$img) {
            exit;
        }

        $images[] = $img;
        $total_width += imagesx($img);
        $max_height = max($max_height, imagesy($img));
    }

    $output_image = imagecreate($total_width, $max_height);

    $transparent = imagecolorallocate($output_image, 255, 0, 255);
    imagefill($output_image, 0, 0, $transparent);
    imagecolortransparent($output_image, $transparent);

    $current_x = 0;
    foreach ($images as $img) {
        $w = imagesx($img);
        $h = imagesy($img);

        imagecopy($output_image, $img, $current_x, 0, 0, 0, $w, $h);

        $current_x += $w;
        imagedestroy($img);
    }

    header('Content-Type: image/gif');

    if (isset($_GET['download']) && $_GET['download'] == '1') {
        header('Content-Disposition: attachment; filename="unit_' . $input . '.gif"');
    } else {
        header('Content-Disposition: inline; filename="unit_' . $input . '.gif"');
    }

    imagegif($output_image);
    imagedestroy($output_image);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unit Number Generator with Live Preview</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f4f4f9; }
        .form-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 320px; }
        input[type="text"] { width: 100%; padding: 8px; margin: 10px 0; box-sizing: border-box; }
        .btn { background: #007BFF; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; cursor: pointer; text-align: center; display: inline-block; text-decoration: none; box-sizing: border-box; margin-top: 5px;}
        .btn:hover { background: #0056b3; }
        .preview-box { margin-top: 15px; padding: 10px; background: #eee; border-radius: 4px; text-align: center; min-height: 60px; display: flex; align-items: center; justify-content: center; }
        .preview-box img { max-width: 100%; height: auto; border: 1px solid #ccc; }
        .placeholder-text { color: #888; font-size: 13px; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Unit Number Generator</h2>

    <form id="unitForm" action="?number=" method="GET">
        <label for="unit_number">Enter Unit Number:</label>
        <input type="text" id="unit_number" maxlength="5" name="number" placeholder="e.g. 405" autocomplete="off" oninput="updatePreview()">

        <div class="preview-box" id="previewContainer">
            <span class="placeholder-text">Preview will appear here</span>
        </div>
        <input type="hidden" name="download" value="1">
        <button type="submit" class="btn" id="downloadBtn" style="display:none;">Download GIF</button>
    </form>
</div>
<script>
function updatePreview() {
    const val = document.getElementById('unit_number').value.replace(/[^0-9]/g, '');
    const container = document.getElementById('previewContainer');
    const downloadBtn = document.getElementById('downloadBtn');

    if (val.length > 0) {
        const img = document.createElement('img');
        img.src = '?number=' + encodeURIComponent(val) + '&t=' + new Date().getTime();

        img.onload = function() {
            container.innerHTML = '';
            container.appendChild(img);
            downloadBtn.style.display = 'block';
        };

        img.onerror = function() {
            container.innerHTML = '<span class="placeholder-text" style="color:red;">Invalid digit files missing</span>';
            downloadBtn.style.display = 'none';
        };
    } else {
        container.innerHTML = '<span class="placeholder-text">Preview will appear here</span>';
        downloadBtn.style.display = 'none';
    }
}
</script>
</body>
</html>
