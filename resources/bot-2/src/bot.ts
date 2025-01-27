import { Client, Guild, GuildChannel, Message, GuildMember, TextChannel } from 'discord.js';
import { inject, injectable } from 'inversify';
import { TYPES } from './ioc/types';
import { MessageResponder } from './services/message-responder';
import { Environment, Settings } from './services/settings';
import Enmap from 'enmap';
import { Patron } from './services/patron';
import { BotCommandHandler } from './services/bot-command-handler';
import { Pool } from 'pg';


@injectable()
export class Bot {
    constructor(
        @inject(TYPES.Client) private client: Client,
        @inject(TYPES.MessageResponder) private messageResponder: MessageResponder,
        @inject(TYPES.BotCommandHandler) private botCommandHandler: BotCommandHandler,
        @inject(TYPES.Token) private token: string,
        @inject(TYPES.Settings) private settings: Settings,
        @inject(TYPES.SettingsDB) private settingsDB: Enmap,
        @inject(TYPES.Patron) private patron: Patron,
        @inject(TYPES.DBPool) private dbPool: Pool,
    ) { }

    public async listen(): Promise<string> {
        console.log(`[BOT] Starting bot with prefix [${this.settings.prefix}] in env [${Environment[this.settings.config.env]}]`);
        this.client.on('ready', async () => await this.onReady());
        this.client.on('message', async message => await this.onMessage(message));
        this.client.on('guildCreate', async guild => await this.onGuildCreate(guild));
        this.client.on('guildMemberAdd', async member => await this.onGuildMemberAdd(member));
        this.client.on('guildMemberUpdate', async (_old, updated) => await this.onGuildMemberUpdate(updated));
        console.info(`Logging into discord`);
        return this.client.login(this.token);
    }

    async onReady() {
        await this.botCommandHandler.init();
        // Get the bot server and load all the members in the cache. Must do this in order
        // for guildMemberAdd to actually work the first time a role is changed
        const botServer = await this.client.guilds.fetch(this.settings.config.botGuild);
        await botServer.members.fetch();
        console.log(`Oranje fetched ${botServer.name} ${botServer.nameAcronym}`);

        const db = await this.dbPool.connect();
        let total = 0;
        let values = [];
        for (const guild of this.client.guilds.cache.values()) {
            total += guild.memberCount;
            console.log(`[READY] In guild ${guild.name} [${guild.memberCount}]`);
            values.concat(`(${guild.id}, ${guild.name})`);
            db.query(`
                insert into servers (server_id, name, created_at, updated_at) values ($1, $2, to_timestamp($3), to_timestamp($3))
                on conflict (server_id) do update set name = excluded.name
            `, [guild.id, guild.name, Math.floor(Date.now() / 1000)]);
        }
        db.release();

        console.log(`[READY] ${this.client.user.tag}, ready to serve ${total} users in ${this.client.guilds.cache.size} servers.`);
        if (this.settings.config.env !== Environment.local) {
            // Make the bot "play the game" which is the help command with default prefix.
            this.client.user.setActivity(`${this.settings.prefix}help (${this.client.guilds.cache.size} servers)`, {type: 'PLAYING'});
        }
    }

    async onMessage(message: Message) {
        if (this.settings.config.env === Environment.local) {
            const name = message.channel instanceof GuildChannel ? (message.channel as GuildChannel).name : 'DM';
            const name2 = message.channel instanceof GuildChannel ? (message.channel as GuildChannel).parent?.name : 'DM';
            console.debug(`🍺 [${message.guild?.name ?? 'DM'}] [${name2 ?? 'TOP'}] [${name}] [${message.member?.displayName ?? message.author.username}]\n  🗞 ${message.content ?? message.embeds}`);
        }

        try {
            await this.messageResponder.handle(message);
        } catch (e) {
            if (e) {
                console.error('Unhandled message', e);
            }
        }
    }

    async onGuildCreate(guild: Guild) {
        console.log(`[GUILD JOIN] ${guild.name} (${guild.id}) added the bot. Owner: ${guild.owner.user.tag} (${guild.owner.user.id})`);
    }

    async onGuildLeave(guild: Guild) {
        console.log(`[GUILD LEAVE] ${guild.name} (${guild.id}) removed the bot.`);
        if (this.settingsDB.has(guild.id)) {
            this.settingsDB.delete(guild.id);
        }
    }

    async onGuildMemberAdd(member: GuildMember) {
        const guildSettings = this.settings.guildSettings(member.guild);

        if (guildSettings.welcomeEnabled !== 'true') {
            return;
        }
        const welcomeMessage = guildSettings.welcomeMessage.replace('{{user}}', member.user.tag);
        const welcomeChannel = member.guild.channels.cache.find(c => c.name === guildSettings.welcomeChannel) as TextChannel;
        await welcomeChannel.send(welcomeMessage).catch(console.error);
    }

    async onGuildMemberUpdate(member: GuildMember) {
        if (!member.guild && member.partial) {
            await member.fetch();
        }

        if (!member.guild || member.guild.id !== this.settings.config.botGuild) { return; }

        await this.patron.updatePatronLevelFor(member);
        console.log(`Patron level for ${member.displayName}: ${await this.patron.patronLevelFor(member)}`);
    }
}
