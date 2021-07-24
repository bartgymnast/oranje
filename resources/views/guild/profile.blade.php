@extends('layouts.app')
@section('title')—Guild Profiles @endsection
@section('body-class', 'no-bg')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header row no-margin justify-content-between align-items-start">
                    <div class="column">
                        <h2>Guild Discord Configuration</h2>
                        <div class="small-note">Only officers can update these items</div>
                    </div>
                    <div class="row no-margin justify-content-end align-items-center">
                        <button class="btn btn-primary btn-icon striped" @@click="go(`https://discordapp.com/oauth2/authorize?client_id=454401959777271819&scope=bot&permissions=268954688`, true)">
                            <ion-icon name="server" size="medium"></ion-icon>
                        </button>
                        <form action="{{ route('guild.members.update') }}" method="POST">
                            @csrf

                            <button class="btn btn-secondary btn-icon-text striped">
                                <div class="row no-margin align-items-center">
                                    <ion-icon name="sync" size="small"></ion-icon> <span>Update Member List</span>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div>Found the following ally codes for discord id {{ auth()->user()->discord_id }}:</div>
                    <ul>
                        @foreach (auth()->user()->accounts as $account)
                            <li>{{ $account->ally_code }}</li>
                        @endforeach
                    </ul>
                </div>

                @foreach($guilds as $guild)
                @if(!is_null($guild->id))
                <div class="card-body">
                    <h2>{{ $guild->name }}</h2>
                    <div class="small-note">Define the ID of the discord server where your guild roles are defined. These roles are used to authorize other guild-related actions.</div>

                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id, 'prop' => 'server_id']) }}`" :label="`Server ID`" :value="`{{ $guild->server_id }}`"></auto-text-field>
                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id, 'prop' => 'admin_channel']) }}`" :label="`Admin Channel ID`" :value="`{{ $guild->admin_channel }}`"></auto-text-field>
                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id, 'prop' => 'officer_role_regex']) }}`" :label="`Officer Role Query (case insensitive)`" :value="`{{ $guild->officer_role_regex }}`"></auto-text-field>
                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id, 'prop' => 'member_role_regex']) }}`" :label="`Member Role Query (case insensitive)`" :value="`{{ $guild->member_role_regex }}`"></auto-text-field>

                    <collapsable start-open>
                        <template #top-trigger="{ open }">
                            <button class="btn btn-primary btn-icon-text inverted">
                                <div class="row no-margin align-items-center">
                                    <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Members</span>
                                </div>
                            </button>
                        </template>
                        @foreach ($guild->members->sortBy('sort_name', SORT_NATURAL|SORT_FLAG_CASE) as $member)
                        <div class="input-group discord-select row no-margin">
                            <auto-select
                                :route="`{{ route('member.update.discord', ['ally' => $member->ally_code]) }}`"
                                :options="{{ $guild->discordMemberOptions()->sortBy('label', SORT_NATURAL|SORT_FLAG_CASE)->values()->toJson() }}"
                                @if ($member->discord->discord_id)
                                :value="`{{ $member->discord->discord_id }}`"
                                @endif
                                :clearable="false"
                                placeholder="Select Discord ID"
                            >
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $member->player }} ({{ $member->ally_code }})</span>
                                </div>
                            </auto-select>
                        </div>
                        @endforeach
                    </collapsable>
                </div>
                @endif
                @endforeach

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')
