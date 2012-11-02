<?php
namespace Embed\Services;

use Embed\Url;
use Embed\Providers\Html;
use Embed\Providers\OEmbed;

abstract class OEmbedService extends Service {
	public $Url;
	public $Html;
	public $OEmbed;

	static public function check (Url $Url) {
		return $Url->match(static::$settings['oembed']['patterns']) ? $Url : false;
	}

	public function __construct (Url $Url) {
		$this->Url = $Url;
		$this->OEmbed = new OEmbed($Url, static::$settings['oembed']);

		if (!$this->OEmbed->isEmpty()) {
			$this->Html = new Html($Url);
			$this->setData();
		}
	}

	public function hasData () {
		return $this->OEmbed->isEmpty() ? false : true;
	}

	protected function setData () {
		$this->width = $this->OEmbed->get('width');
		$this->height = $this->OEmbed->get('height');
		$this->title = $this->OEmbed->get('title') ?: $this->Html->get('title');
		$this->description = $this->OEmbed->get('description') ?: $this->Html->get('description');
		$this->code = $this->OEmbed->get('html');
		$this->url = $this->Html->get('canonical') ?: $this->Url->getUrl();
		$this->type = $this->OEmbed->get('type');
		$this->authorName = $this->OEmbed->get('author_name');
		$this->authorUrl = $this->OEmbed->get('author_url');
		$this->providerIcon = $this->Html->get('icon');
		$this->providerName = $this->OEmbed->get('provider_name');
		$this->providerUrl = $this->OEmbed->get('provider_url');

		if (($this->type === 'photo') && $this->OEmbed->get('url')) {
			$this->image = $this->OEmbed->get('url');
		} else {
			$this->image = $this->OEmbed->get('thumbnail_url');
		}

		if ($this->width && (strpos($this->width, '%') === false) && $this->height && (strpos($this->height, '%') === false)) {
			$this->aspectRatio = ($this->height / $this->width) * 100;
		}

		//Clear extra code
		if (($html = $this->code)) {
			if (strpos($html, '</iframe>') !== false) {
				$html = preg_replace('|^.*(<iframe.*</iframe>).*$|', '$1', $html);
			} else if (strpos($html, '</object>') !== false) {
				$html = preg_replace('|^.*(<object.*</object>).*$|', '$1', $html);
			} else if (strpos($html, '</embed>') !== false) {
				$html = preg_replace('|^.*(<embed.*</embed>).*$|', '$1', $html);
			}

			$this->code = $html;
		}
	}
}