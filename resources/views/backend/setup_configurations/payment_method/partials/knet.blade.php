<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6 ">{{ translate('Knet Credential') }}</h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('payment_method.update') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_method" value="knet">

                <div class="form-group row">
                    <input type="hidden" name="types[]" value="KNET_CLIENT_ID">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Knet Client Id') }}</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="KNET_CLIENT_ID"
                            value="{{ env('KNET_CLIENT_ID') }}"
                            placeholder="{{ translate('Knet Client ID') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <input type="hidden" name="types[]" value="KNET_CLIENT_SECRET">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Knet Client Secret') }}</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="KNET_CLIENT_SECRET"
                            value="{{ env('KNET_CLIENT_SECRET') }}"
                            placeholder="{{ translate('Knet Client Secret') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <input type="hidden" name="types[]" value="KNET_ENCRP_KEY">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Knet Encryption Key') }}</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="KNET_ENCRP_KEY"
                            value="{{ env('KNET_ENCRP_KEY') }}"
                            placeholder="{{ translate('Knet Encryption Key') }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <input type="hidden" name="types[]" value="KNET_ENVIRONMENT">
                    <div class="col-md-4">
                        <label class="col-from-label">{{ translate('Knet Sandbox Mode') }}</label>
                    </div>
                    <div class="col-md-8">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input value="development" name="KNET_ENVIRONMENT" type="checkbox"
                                @if (env('KNET_ENVIRONMENT') == 'development') checked @endif>
                            <span class="slider round"></span>
                        </label>
                        <small class="text-muted d-block mt-1">Uncheck for production mode</small>
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{ translate('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
