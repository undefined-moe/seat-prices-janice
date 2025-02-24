@extends('web::layouts.app')

@section('title', trans('janicepriceprovider::janice.edit_price_provider'))
@section('page_header', trans('janicepriceprovider::janice.edit_price_provider'))
@section('page_description', trans('janicepriceprovider::janice.edit_price_provider'))

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ trans('janicepriceprovider::janice.edit_price_provider') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('janicepriceprovider::configuration.post') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">

            <div class="form-group">
                <label for="name">{{ trans('pricescore::settings.name') }}</label>
                <input required type="text" name="name" id="name" class="form-control" placeholder="{{ trans('pricescore::settings.name_placeholder') }}" value="{{ $name ?? '' }}">
            </div>

            <div class="form-group">
                <label for="apikey">{{ trans('janicepriceprovider::janice.apikey') }}</label>
                <input required type="text" name="apikey" id="apikey" class="form-control" value="{{ $apikey ?? '' }}">
                <small class="form-text text-muted">{{ trans('janicepriceprovider::janice.apikey_description') }}</small>
            </div>

            <div class="form-group">
                <label for="immediate">{{ trans('janicepriceprovider::janice.immediate') }}</label>
                <select id="immediate" name="immediate" class="form-control" style="width: 100%;">
                    <option value="immediate" @if($immediate==='immediate' ) selected @endif>{{ trans('janicepriceprovider::janice.immediate') }}</option>
                    <option value="5percent" @if($region==='5percent' ) selected @endif>{{ trans('janicepriceprovider::janice.5percent') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label for="region">{{ trans('janicepriceprovider::janice.region') }}</label>
                <select id="region" name="region" class="form-control" style="width: 100%;">
                    <option value="2" @if($region===2 ) selected @endif>Jita 4-4</option>
                    <option value="3" @if($region===3 ) selected @endif>R1O-GN</option>
                    <option value="6" @if($region===6 ) selected @endif>NPC</option>
                    <option value="114" @if($region===114 ) selected @endif>MJ-5F9</option>
                    <option value="115" @if($region===115 ) selected @endif>Amarr</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price_type">{{ trans('janicepriceprovider::janice.price_type') }}</label>
                <select name="price_type" id="price_type" class="form-control" required>
                    <option value="sell" @if($price_type==='sell' ) selected @endif>{{ trans('janicepriceprovider::janice.sell') }}</option>
                    <option value="buy" @if($price_type==='buy' ) selected @endif>{{ trans('janicepriceprovider::janice.buy') }}</option>
                    <option value="split" @if($price_type==='split' ) selected @endif>{{ trans('janicepriceprovider::janice.split') }}</option>

                    <option value="sell5" @if($price_type==='sell5' ) selected @endif>{{ trans('janicepriceprovider::janice.sell5') }}</option>
                    <option value="buy5" @if($price_type==='buy5' ) selected @endif>{{ trans('janicepriceprovider::janice.buy5') }}</option>
                    <option value="split5" @if($price_type==='split5' ) selected @endif>{{ trans('janicepriceprovider::janice.split5') }}</option>

                    <option value="sell30" @if($price_type==='sell30' ) selected @endif>{{ trans('janicepriceprovider::janice.sell30') }}</option>
                    <option value="buy30" @if($price_type==='buy30' ) selected @endif>{{ trans('janicepriceprovider::janice.buy30') }}</option>
                    <option value="buy30" @if($price_type==='split30' ) selected @endif>{{ trans('janicepriceprovider::janice.split30') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label for="allow_empty">{{ trans('janicepriceprovider::janice.allow_empty') }}</label>
                <select name="allow_empty" id="allow_empty" class="form-control" required>
                    <option value="yes" @if($allow_empty==='yes' ) selected @endif>{{ trans('janicepriceprovider::yes') }}</option>
                    <option value="no" @if($price_type==='no' ) selected @endif>{{ trans('janicepriceprovider::no') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cache">{{ trans('janicepriceprovider::janice.cache') }}</label>
                <input required type="number" name="cache" id="cache" class="form-control" value="{{ $cache ?? '12' }}">
            </div>

            <div class="form-group">
                <label for="timeout">{{ trans('janicepriceprovider::janice.timeout') }}</label>
                <input required type="number" name="timeout" id="timeout" class="form-control" placeholder="{{ trans('pricescore::settings.timeout_placeholder') }}" value="{{ $timeout ?? 5 }}" min="0" step="1">
                <small class="form-text text-muted">{{ trans('janicepriceprovider::janice.timeout_description') }}</small>
            </div>

            <button type="submit" class="btn btn-primary">{{ trans('pricescore::priceprovider.save')  }}</button>
        </form>
    </div>
</div>

@endsection


@push('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var market_prices_region = $('#region');
        market_prices_region.select2();
    });
</script>
@endpush