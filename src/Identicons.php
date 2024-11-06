<?php

namespace Identicons;

use GdImage;

class Identicons
{
    /**
     * @var int
     */
    private int $size;

    /**
     * @var int
     */
    private int $cells;

    /**
     * @var int
     */
    private int $quart;

    /**
     * @var int
     */
    private int $r;

    /**
     * @var int
     */
    private int $g;

    /**
     * @var int
     */
    private int $b;

    /**
     * @var int $size
     * @var int $cells
     * @var int $r
     * @var int $g
     * @var int $b
     */
    public function __construct(
        int $size = 24,
        int $cells = 8,
        int $r = 245,
        int $g = 245,
        int $b = 245
    ) {
        $this->size = $size;
        $this->cells = $cells;
        $this->quart = $this->size * $this->cells;
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }

    /**
     * Method to call for generating an identicon from a raw string
     * Hashes the string in sha256, creating a 64-bit hash and therefore a 8x8 grid.
     * Returns a GdImage which you can save or serve in your preferred manner.
     *
     * @var string $str
     *
     * @return GdImage
     */
    public function generateFromString(string $str): GdImage
    {
        $hash = hash('sha256', $str);
        $image = imagecreate($this->quart, $this->quart);

        $generatedColor = $this->getColorFromHash($image, $hash);
        $backgroundColor = $this->getDefaultColor($image);

        $row = 0;
        $column = 0;
        foreach (str_split($hash) as $hex) {
            if ($column > ($this->cells - 1)) {
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
                $row * $this->size,
                $column * $this->size,
                $row * $this->size + $this->size,
                $column * $this->size + $this->size,
                $color
            );

            $column++;
        }

        $topRight = $this->createDuplicate($image, IMG_FLIP_HORIZONTAL, $this->quart);
        $bottomLeft = $this->createDuplicate($image, IMG_FLIP_VERTICAL, $this->quart);
        $bottomRight = $this->createDuplicate($image, IMG_FLIP_BOTH, $this->quart);

        // knit together into an identicon
        $fullSize = $this->quart * 2;
        $identicon = imagecreate($fullSize, $fullSize);

        imagecopymerge(
            $identicon,
            $image,
            0,
            0,
            0,
            0,
            $this->quart,
            $this->quart,
            100
        );

        imagecopymerge(
            $identicon,
            $topRight,
            $this->quart,
            0,
            0,
            0,
            $this->quart,
            $this->quart,
            100
        );

        imagecopymerge(
            $identicon,
            $bottomLeft,
            0,
            $this->quart,
            0,
            0,
            $this->quart,
            $this->quart,
            100
        );

        imagecopymerge(
            $identicon,
            $bottomRight,
            $this->quart,
            $this->quart,
            0,
            0,
            $this->quart,
            $this->quart,
            100
        );

        return $identicon;
    }

    /**
     * @param GdImage $image
     * @var string $hash
     *
     * @return int
     */
    private function getColorFromHash(
        GdImage $image,
        string $hash
    ): int {
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        return imagecolorallocate($image, $r, $g, $b);
    }

    /**
     * @var GdImage $image
     * 
     * @return int
     */
    private function getDefaultColor(GdImage $image): int
    {
        return imagecolorallocate($image, $this->r, $this->g, $this->b);
    }

    /**
     * @var GdImage $image
     * @var int $size
     * @var int $mode
     * 
     * @return GdImage
     */
    private function createDuplicate(
        GdImage $image,
        int $size,
        int $mode
    ): GdImage {
        $dupe = imagecreate($size, $size);
        imagecopy($dupe, $image, 0, 0, 0, 0, $size, $size);
        imageflip($dupe, $mode);
        
        return $dupe;
    }
}
