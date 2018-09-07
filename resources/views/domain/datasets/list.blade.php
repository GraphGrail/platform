<div class="m-portlet">
    <div class="m-portlet__body">
        <!--begin::Section-->
        <div class="m-section">
					<span class="m-section__sub">
                        @lang($title ?? 'Datasets')
					</span>
            <div class="m-section__content">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('File')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datasets as $dataset)
                        <tr>
                            <th scope="row">{{ $dataset->id }}</th>
                            <td><a href="{{ route('datasets.show', ['dataset' => $dataset]) }}">{{ $dataset->getFullName() }}</a></td>
                            <td>{{ $dataset->statusLabel() }}</td>
                            <td>
                                @if($dataset->user_id)
                                    <a href="javascript:void(0);" onclick="deleteDataset({{ $dataset->id }}, '{{ $dataset->getFullName() }}')">
                                        <i class="fa fa-trash" title="@lang('Delete')"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--end::Section-->
    </div>
    <!--end::Form-->
</div>
@section('scripts')
    @parent

    <script language="javascript">
        function deleteDataset(id, name) {
            if (!confirm("@lang('Are you sure?')" + " " + "@lang('Delete')" + " " + name + "?")) {
                return;
            }
            const url = "{{ url('datasets') }}/" + id;
            axios.delete(url)
                .then(function (response) {
                    window.location.reload();
                })
                .catch(function (error) {
                    console.log(error);
                    window.location.reload();
                });
        }
    </script>
@endsection
