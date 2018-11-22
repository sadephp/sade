<?php

use Sade\Component\Tag;

class CustomTemplateTag extends Tag
{
    /**
     * Render template.
     *
     * @return string
     */
    public function render()
    {
        return 'CustomTemplateTag';
    }
}
