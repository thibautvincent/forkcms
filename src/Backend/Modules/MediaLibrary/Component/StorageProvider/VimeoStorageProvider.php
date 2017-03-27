<?php

namespace Backend\Modules\MediaLibrary\Component\StorageProvider;

use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;

class VimeoStorageProvider implements StorageProviderInterface
{
    /** @var string */
    protected $linkURL;

    /** @var string */
    protected $includeURL;

    /**
     * YoutubeStorageProvider constructor.
     *
     * @param string $linkURL
     * @param string $includeURL
     */
    public function __construct(string $linkURL, string $includeURL)
    {
        $this->linkURL = $linkURL;
        $this->includeURL = $includeURL;
    }

    /**
     * @param MediaItem $mediaItem
     * @return string
     */
    public function getAbsolutePath(MediaItem $mediaItem): string
    {
        return $this->getAbsoluteWebPath($mediaItem);
    }

    /**
     * @param MediaItem $mediaItem
     * @return string
     */
    public function getAbsoluteWebPath(MediaItem $mediaItem): string
    {
        return $this->getWebPath($mediaItem);
    }

    /**
     * @param MediaItem $mediaItem
     * @return string
     */
    public function getIncludeHTML(MediaItem $mediaItem): string
    {
        return '<iframe src="' . $this->includeURL . $mediaItem->getUrl() . '?color=ffffff&title=0&byline=0&portrait=0&badge=0" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
    }

    /**
     * @param MediaItem $mediaItem
     * @return string
     */
    public function getLinkHTML(MediaItem $mediaItem): string
    {
        return '<a href="' . $this->getAbsoluteWebPath($mediaItem) . '" title="' . $mediaItem->getTitle() . '" target="_blank">' . $mediaItem->getTitle() . '</a>';
    }

    /**
     * @param MediaItem $mediaItem
     * @return string
     */
    public function getWebPath(MediaItem $mediaItem): string
    {
        return $this->linkURL . $mediaItem->getUrl();
    }
}
