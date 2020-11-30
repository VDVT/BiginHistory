<?php

namespace Bigin\History;

use Assets;

class History
{
    /**
     * addAuditHistoryTab
     *
     * @param  Model $data
     * @return void
     */
    public function addAuditHistoryTab($data = null)
    {
        if (!empty($data)) {
            Assets::addStylesDirectly('/vendor/core/packages/revision/css/revision.css');
            return  view('history.history-tab')->render();
        }
    }

    /**
     * addAuditHistoryContent
     *
     * @param  Model $data
     * @return void
     */
    public  static function addAuditHistoryContent($data = null)
    {
        if (!empty($data)) {
            return view('history.history-content', ['model' => $data])->render();
        }
    }
}
