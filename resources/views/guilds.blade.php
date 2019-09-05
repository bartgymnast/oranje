@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">

@auth('admin')
            <div class="card">
                <div class="card-header"><h2>Add a guild</h2></div>

                <div class="card-body">
                @if (session('guildStatus'))
                    <div class="alert alert-success">
                        {{ session('guildStatus') }}
                    </div>
                @endif
                    <p>Enter the ID of the guild from its swgoh.gg URL (e.g. 3577)</p>
                    <form method="POST" action="{{ route('guild.add') }}" >
                        @csrf
                        <div class="row add-row">
                            <input type="text" name="guild">
                            <button type="submit" class="btn btn-primary">{{ __('Add Guild') }}</button>
                        </div>
                    </form>
                </div>
            </div>
@endauth

            <div class="card">
                <div class="card-header"><h2>Schwartz Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', true) as $guild)
                        <div class="row">
                            <div>
                                <div>{{ $guild->name }}</div>
                                <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
                            </div>

                            <guild-teams :id="{{ $guild->id }}"></guild-teams>

                            <a href="{{ $guild->url }}" target="_gg" class="gg-link">
                                @include('shared.bb8')
                            </a>
                            <form method="POST" action="{{ route('guild.refresh', ['guild' => $guild->id]) }}">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-refresh-circle"></i></button>
                            </form>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Other Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', false) as $guild)
                        <div class="row">
                            <div>
                                <div>{{ $guild->name }}</div>
                                <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
                            </div>

                            <guild-teams :id="{{ $guild->id }}"></guild-teams>

                            <a href="{{ $guild->url }}" target="_gg" class="gg-link">
                                @include('shared.bb8')
                            </a>
                            <form method="POST" action="{{ route('guild.refresh', ['guild' => $guild->id]) }}">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-refresh-circle"></i></button>
                            </form>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')