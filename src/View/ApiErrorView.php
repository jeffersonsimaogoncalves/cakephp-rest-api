<?php

namespace RestApi\View;

use Cake\View\View;

/**
 * Class ApiErrorView
 *
 * Default view class for error
 *
 * @package RestApi\View
 */
class ApiErrorView extends View
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
     * Renders custom api error view
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

        $this->layout = 'RestApi.error';

        $this->Blocks->set('content', $this->renderLayout('', $this->layout));

        $this->hasRendered = true;

        return $this->Blocks->get('content');
    }
}
