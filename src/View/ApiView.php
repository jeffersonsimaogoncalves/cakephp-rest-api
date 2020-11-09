<?php

namespace RestApi\View;

use Cake\View\JsonView;

/**
 * Api View
 *
 * Default view class for rendering API response
 *
 * @package RestApi\View
 */
class ApiView extends JsonView
{
    /**
     * @var boolean
     */
    private $hasRendered;

    public function initialize(): void
    {
        parent::initialize();

        $this->response->withType('json');
    }

    /**
     * Renders api response
     *
     * @param  string|null  $template  Name of view file to use
     * @param  string|null  $layout  Layout to use.
     *
     * @return string|null Rendered content or null if content already rendered and returned earlier.
     * @throws \Cake\Core\Exception\Exception If there is an error in the view.
     */
    public function render(?string $template = null, $layout = null): string
    {
        if ($this->hasRendered) {
            return '';
        }

        $this->layout = 'RestApi.response';

        $this->Blocks->set('content', $this->renderLayout('', $this->layout));

        $this->hasRendered = true;

        return $this->Blocks->get('content');
    }
}
