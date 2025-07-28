
class GameBot {
    constructor(username, channels, container, ignore = [], secret = 'je637klvvxqnfhjoomumuh6eygcr8c') {
        this.cmd = [];
        this.ignore = [];
        this.init = null;
        this.messages=[];
        this.mInterval=5;
        this.messageEvent=[];
        this.mIndex=0;
        this.var_array={};
        this.container = container;
        this.channel = channels[0];
        if (typeof tmi === 'object') {
            this.created = true;
            this.client = new tmi.Client({
                options: { debug: true },
                identity: {
                    username: username,
                    password: `oauth:${secret}`
                },
                channels: channels
            });
        }
        else
            console.error("No tmi library Found");
    }
    addVariable=function(name,variable){
        this.var_array[name]=variable;
    }
    variable=function(name){
        if(this.var_array[name]==undefined)
            throw "No variable access";
        return this.var_array[name];
    }
    async setMessageFromFile() {
        try {
            // Effectuer une requête pour récupérer les messages
            const response = await fetch(`ajax.php?act=getMessages&channel=${this.channel}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8',
                },
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const result = await response.json();
            if (result.result === "OK") {

                this.messages = this.messages.concat(result.data);
                return this.messages;
            } else {
                console.error(`Error from API: ${result.errString}`);
                return Promise.reject(`Error from API: ${result.errString}`);
            }
        } catch (error) {
            console.error(`Failed to fetch messages: ${error.message}`);
            return Promise.reject(error);
        }
    }
    pushMessage=async function(msg) {
        let response = await fetch('ajax.php?act=addMessage&channel='+this.channel, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({message:msg})
        });
        let result = await response.json();
        return (result.message);
    }
    setMessage= function(msg,commit=false){
        this.messages.push(msg);
        if(commit){
            this.pushMessage(msg);
        }
    }
    
    getMessages=function(){return this.messages;}
    getCmd = function () {
        return this.cmd;
    }
    setInit(fnc) {
        this.init = fnc;
    }
    init = function () {
        this.init.call(this);
    }
    message(message) {
        if (this.created)
            this.client.say(this.channel, `${message}`);
        else
            console.error("GameBot can't post message : No instance of tmi client")

    }
    isIgnore(username) {
        return this.ignore.includes(username);
    }
    setIgnore(username) {
        this.ignore.push(username);
    }
    setCommand(cmd, callback) {

        if (typeof cmd == "string") {
            if (this.cmd[cmd.toLocaleLowerCase()] != undefined) {
                console.error(`${cmd} already exist in current commands`);
                return false;
            }
            this.cmd[cmd.toLocaleLowerCase()] = callback;
            console.info(`${cmd.toLocaleLowerCase()} a été incorporée.`)
            return;
        } else {
            cmd.forEach(element => {
                if (this.cmd[element] != undefined) {
                    console.error(`${cmd.toLocaleLowerCase()} already exist in current commands`);
                    return;
                }
                this.cmd[element.toLocaleLowerCase()] = callback;
                console.info(`${element.toLocaleLowerCase()} a été incorporée.`)
            });
        }
    }
    getBot = function () {
        return this.client;
    }
    readFile = async function (file,fnc) {
        let data = {
            file: file
        }
        let response = await fetch('ajax.php?act=readFile', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(data)
        });
        let result = await response.json();
        if (result.result == "OK")
            fnc.call(this,result.data);
        else
            return false;
    }
    writeFile = async function (file, msg) {
        let data = {
            file: file,
            message: msg
        }
        let response = await fetch('ajax.php?act=fileWrite&overwrite=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(data)
        });
        let result = await response.json();
        return (result.message);
    }
    diffuseMessages=function(){
       // debugger
        if(this.messages.length!=0){
            console.info("Il y a "+this.messages.length+" messages");
            setInterval(()=>{
                console.dir(this.messages);
                this.mIndex=(this.mIndex+1)%this.messages.length;
                if(this.messages[this.mIndex].byPass!=undefined){
                    delete this.messages[this.mIndex].byPass;
                }else
                    this.message(this.messages[this.mIndex].msg);
            },this.mInterval*60*1000);
        }
    }
    onMessage=function(fnc){
        this.messageEvent.push(fnc);
    }
    openBot = function () {
        if (this.created) {
            this.client.connect();
           
            this.client.on('message', (channel, tags, message, self) => {

                if (self) return; // Ignore les messages du bot
                if (this.ignore.includes(tags.username)) {//Ignore les autres bots
                    return;
                }
                if(this.messageEvent.length)
                    this.messageEvent.forEach(fnc=>{
                fnc.call(this,tags,message);
                })
                const args = message.split(' ');
                const command = args.shift().toLowerCase();

                if (this.cmd.includes(command)) {//Ignore les commandes non prise en charge
                    return
                }
                console.clear();//Raffraichi la console
               // console.dir(this.cmd, command.toString());
                console.log(this.cmd.includes(command.toString()));
                this.cmd[command].call(this, args, tags, channel);

            });
        } else {
            console.error("GameBot can't open, No instance of tmi client")
        }
    }

}