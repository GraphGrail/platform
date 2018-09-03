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
                        <th>File</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datasets as $dataset)
                        <tr>
                            <th scope="row">{{ $dataset->id }}</th>
                            <td><a href="{{ route('datasets.show', ['dataset' => $dataset]) }}">{{ $dataset->getFullName() }}</a></td>
                            <td>{{ $dataset->statusLabel() }}</td>
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
