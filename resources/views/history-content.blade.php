<div class="tab-pane" id="tab_history">
    <div class="form-group" style="min-height: 400px;">
        <table class="table table-bordered table-striped" id="table">
            <thead>
                <tr>
                    <th>{{ trans('core/base::tables.author') }}</th>
                    <th align="center">{{ trans('core/base::forms.content') }}</th>
                    <th>{{ trans('core/base::tables.created_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @if ($model->audithistory !== null && count($model->audithistory)>0)
                    @foreach($model->audithistory as $history)
                        <tr>
                            <td style="min-width: 145px;">{{ $history->userResponsible() ? $history->userResponsible()->getFullName() : 'Khách hàng' }}</td>
                            <td>{{ $history->details ?? '' }}</td>
                            <td style="min-width: 145px;">{{ date_from_database($history->created_at, config('core.base.general.date_format.date_time')) }}</td> 
                        </tr>
                    @endforeach
                @else
                    <tr class="text-center">
                        <td colspan="5">{{ trans('core/base::tables.no_record') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
