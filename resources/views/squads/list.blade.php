@extends('layouts.app')
@section('title', '—Squads')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header"><h2>Squads</h2></div>

                <squad-tabs
                    :groups="{{ $groups->toJson() }}"
                    :guilds="{{ $guilds->toJson() }}"
                    :selected="{{ $squad->id }}"
                ></squad-tabs>

                <div class="card-header row justify-content-between align-items-baseline child-no-margin">
                    <div class="column"><h4>{{ $squad->name }}</h4><div class="small-note"><strong>{{ $squad->guild_id === 0 ? "Global" : "{$squad->guild->name}" }}</strong> Squad Group</div></div>
                    <auto-checkbox :route="`{{ route('squads.group.publish', ['squad' => $squad->id]) }}`" {{ $squad->publish ? 'checked ' : '' }}:label="`{{ $squad->guild_id !== 0 ? "Publish to guild list" : "Publish globally" }}`"></auto-checkbox>
                </div>

                <collapsable {{ $chars->count() == 0 && $ships->count() == 0 ? 'start-open' : '' }}>
                    <template #top-trigger="{ open }">
                        <button class="btn btn-primary btn-icon-text">
                            <div class="row no-margin align-items-center">
                                <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Add a Squad</span>
                            </div>
                        </button>
                    </template>

                    <div class="card-body">
                        <form method="POST" action="{{ route('squads.add') }}" >
                            @csrf
                            <div class="row add-row input-group add-squad-row">
                                <input type="hidden" name="leader_id" value="" id="leader_id">
                                <input type="hidden" name="group" value="{{ $squad->id }}">
                                <unit-select :placeholder="`Leader`" @@input="val => set('leader_id', val ? val.base_id : null)"></unit-select>
                                <input class="form-control" type="text" placeholder="Squad Name" name="name">
                                <input class="form-control" type="text" placeholder="Squad Description" name="description">
                                <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
                            </div>
                            <div class="row add-row input-group add-squad-row multiple">
                                <input type="hidden" name="other_members" value="" id="other_members">
                                <unit-select multiple :placeholder="`Other Members`" @@input="val => set('other_members', val.map(u => u.base_id).reduce((c, u) => [c, u].join(',')))"></unit-select>
                            </div>
                        </form>
                    </div>
                </collapsable>

                @foreach ([$chars, $ships] as $squads)
                    @if ($squads->count() > 0)
                    <div class="card-body squad-list-body">
                        <table class="squad-table">
                            <thead>
                                <tr>
                                    <th class="blank">&nbsp;</th>
                                    <th><span>Team</span></th>
                                    <th><span>Leader</span></th>
                                    <th colspan="{{ count($squads->max('additional_members')) }}"><span>Members</span></th>
                                    <th class="blank">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($squads as $squad)
                                    <tr class="squad-row">
                                        <td class="blank">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" v-model="selectedSquadArray" :value="{{ $squad->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="description-wrapper">
                                                <h5>{{ $squad->display }}</h5>
                                                <div class="small-note">{{ $squad->description }}</div>
                                            </div>
                                        </td>
                                        <td class="top">
                                            <div class="column char-image-column">
                                                <div class="char-image-square medium {{ $units->where('base_id', $squad->leader_id)->first()->alignment }}">
                                                    <img src="/images/units/{{$squad->leader_id}}.png">
                                                </div>
                                                <div class="char-name">{{ $units->where('base_id', $squad->leader_id)->first()->name }}</div>
                                            </div>
                                        </td>
                                        @forelse ($squad->additional_members as $char_id)
                                            <td class="top">
                                                <div class="column char-image-column">
                                                    <div class="char-image-square medium {{ $units->where('base_id', $char_id)->first()->alignment }}">
                                                        <img src="/images/units/{{$char_id}}.png">
                                                    </div>
                                                    <div class="char-name">{{ $units->where('base_id', $char_id)->first()->name }}</div>
                                                </div>
                                            </td>
                                        @empty
                                            <td>&nbsp;</td>
                                        @endforelse
                                        @for ($i = 0; $i < count($squads->max('additional_members')) - count($squad->additional_members); $i++)
                                            <td>&nbsp;</td>
                                        @endfor
                                        <td class="blank">
                                            <form class="column justify-content-center align-items-center" method="POST" action="{{ route('squad.delete', ['id' => $squad->id ]) }}">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-icon"><ion-icon name="trash" size="medium"></ion-icon></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    @endif
                @endforeach

                @if ($chars->count() == 0 && $ships->count() == 0)
                    <div class="card-body">
                        <h4>No squads configured</h4>
                    </div>
                @else
                <collapsable>
                    <form method="POST" :action="`/squads/message/${messageChannel}`">
                        @method('PUT')
                        @csrf
                        <div class="row no-margin add-row input-group align-items-baseline">
                            <input type="hidden" name="squads" :value="selectedSquadArray.join(',')">
                            <label for="discord-channel">Discord Channel ID:</label>
                            <input class="form-control" id="discord-channel" type="text" placeholder="Channel ID" v-model="messageChannel">
                            <button type="submit" class="btn btn-primary">{{ __('Send Messages') }}</button>
                        </div>
                    </form>

                    <template #top-trigger="{ open }">
                        <button class="btn btn-primary btn-icon-text">
                            <div class="row no-margin align-items-center">
                                <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Send add messages for checked squads</span>
                            </div>
                        </button>
                    </template>
                </collapsable>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
