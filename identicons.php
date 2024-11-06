<?php

// box size
$size = '24';

// A quarter of the image once made symetrical
$quart = $size * 8;

$string = 'karl stuart minton';

$hash = hash('sha256', $string);

$r = hexdec(substr($hash, 0, 2));
$g = hexdec(substr($hash, 2, 2));
$b = hexdec(substr($hash, 4, 2));

$image = imagecreate($quart, $quart);

$generatedColor = imagecolorallocate($image, $r, $g, $b);
$backgroundColor = imagecolorallocate($image, 245, 245, 245);

$row = 0;
$column = 0;
foreach (str_split($hash) as $hex) {
    if ($column > 7) {
        $row++;
        $column = 0;
    }

    $value = hexdec($hex);

    $color = $backgroundColor;
    if ($value >= 8) {
        $color = $generatedColor;
    }

    imagefilledrectangle(
        $image,
        $row * $size,
        $column * $size,
        $row * $size + $size,
        $column * $size + $size,
        $color
    );

    $column++;
}

$createDuplicate = function (GdImage $image, int $mode, int $size) {
    $dupe = imagecreate($size, $size);
    imagecopy($dupe, $image, 0, 0, 0, 0, $size, $size);
    imageflip($dupe, $mode);
    
    return $dupe;
};

$topRight = $createDuplicate($image, IMG_FLIP_HORIZONTAL, $quart);
$bottomLeft = $createDuplicate($image, IMG_FLIP_VERTICAL, $quart);
$bottomRight = $createDuplicate($image, IMG_FLIP_BOTH, $quart);

// knit together into a identicon
$fullSize = $quart * 2;
$identicon = imagecreate($fullSize, $fullSize);

imagecopymerge(
    $identicon,
    $image,
    0,
    0,
    0,
    0,
    $quart,
    $quart,
    100
);

imagecopymerge(
    $identicon,
    $topRight,
    $quart,
    0,
    0,
    0,
    $quart,
    $quart,
    100
);

imagecopymerge(
    $identicon,
    $bottomLeft,
    0,
    $quart,
    0,
    0,
    $quart,
    $quart,
    100
);

imagecopymerge(
    $identicon,
    $bottomRight,
    $quart,
    $quart,
    0,
    0,
    $quart,
    $quart,
    100
);

imagepng($identicon, __DIR__ . '/identicon.png');
