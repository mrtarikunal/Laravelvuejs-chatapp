<template>
    <div class="card">
        <div class="card-header msg_head">
            <div class="d-flex bd-highlight">
                <div class="img_cont">
                    <img :src="'/img/' + friend.photo" class="rounded-circle user_img">

                </div>
                <div class="user_info">
                    <b :class="{'text-danger':session.block}">
                        Chat with {{friend.name}}<span v-if="isTyping"> is typing...</span>
                        <span v-if="session.block">(Blocked)</span>
                    </b>
                </div>

            </div>



            <div class="dropdown float-right mr-4">
                <a href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-h"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="" @click.prevent="close"><i class="fas fa-times-circle"></i> Close</a>
                    <a class="dropdown-item" href="" @click.prevent="clear"><i class="fas fa-eraser"></i> Clear Chat</a>
                    <a class="dropdown-item" href="" v-if="session.block && can" @click.prevent="unblock"><i class="fa fa-unlock"></i> Unblock</a>
                    <a class="dropdown-item" href="" @click.prevent="block" v-if="!session.block"><i class="fas fa-ban"></i> Block</a>
                </div>
            </div>

        </div>
        <div class="card-body msg_card_body" vue-chat-scroll>
            <span v-for="chat in chats" :key="chat.id">
                <div :class="{'d-flex':chat.type===1, 'justify-content-start':chat.type===1, 'mb-4':chat.type===1, 'text-white': chat.read_at!=null}" v-if="chat.type===1">
                <div class="img_cont_msg">
                    <img :src="'/img/' + friend.photo" class="rounded-circle user_img_msg">
                </div>
                <div class="msg_cotainer">
                    {{chat.message}}
                    <span class="msg_time">{{chat.read_at}}</span>
                </div>
              </div>

                <div :class="{'d-flex':chat.type===0, 'justify-content-end':chat.type===0, 'mb-4':chat.type===0, 'text-white': chat.read_at!=null}" v-if="chat.type===0">
                 <div class="msg_cotainer_send">
                    {{chat.message}}
                    <span class="msg_time">{{chat.read_at}}</span>
                </div>
                    <div class="img_cont_msg">
                    <img :src="'/img/' + userPhoto" class="rounded-circle user_img_msg">
                </div>
              </div>
            </span>

        </div>
        <div class="card-footer">
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text attach_btn"></span>
                </div>
                <textarea v-model="message" :disabled="session.block" class="form-control type_msg" placeholder="Type your message..."></textarea>
                <div class="input-group-append">
                    <span class="input-group-text send_btn" @click.prevent="send"><i class="fas fa-location-arrow"></i></span>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import axios from 'axios';
    export default {
        props: ['friend'],
        data() {
            return {
                chats: [],
                message: null,
                isTyping: false,
                userPhoto: null

            }
        },
        computed: {
          session() {
              return this.friend.session;
          },
            can() {
              return this.session.blocked_by === auth.id;
            }
        },
        watch: {
            message(value) {
                if(value)
                {
                    Echo.private('Chat.' + this.friend.session.id)
                        .whisper('typing', {
                            name: auth.name
                        });
                }
            }
        },
        methods: {
            send() {
                if (this.message) {
                    this.pushToChats(this.message);
                    axios.post('/send/' + this.friend.session.id, {
                        content: this.message,
                        to_user: this.friend.id
                    }).then((res) => (this.chats[this.chats.length-1].id = res.data));

                    this.message = null;
                }
            },
            pushToChats(message) {
                this.chats.push({
                    message: message,
                    type:0,
                    read_at:null,
                    sent_at:"Just now"
                });
            },
            close() {
                this.$emit('close');
            },
            clear() {

                axios.get('/session/clear/' + this.friend.session.id)
                    .then((res) => {
                        this.chats = [];
                    } );
            },
            block() {
                this.session.block = true;
                axios.get('/session/block/' + this.friend.session.id)
                    .then((res) => {
                        this.session.blocked_by = auth.id;
                    } );
            },
            unblock() {
                this.session.block = false;
                axios.get('/session/unblock/' + this.friend.session.id)
                    .then((res) => {
                        this.session.blocked_by = null;
                    } );
            },
            getAllMessages() {
                axios.get('/session/chats/' + this.friend.session.id)
                    .then((res) => (this.chats = res.data.data) );
            },
            read() {
                axios.get('/session/unread/' + this.friend.session.id)
                    .then((res) => console.log(res) );
            },
            getPhoto() {
                this.userPhoto = auth.photo;
            }


        },

        created() {
            this.read();

           this.getAllMessages();

           Echo.private('Chat.' + this.friend.session.id)
                .listen('PrivateChatEvent', (e) => {
                    if(this.friend.session.open)
                    {
                        this.read();
                    }
                    this.chats.push({
                        message: e.content,
                        type:1,
                        sent_at:"Just now"
                    });
                });

            Echo.private('Chat.' + this.friend.session.id)
                .listen('MsgReadEvent', (e) => {
                    this.chats.forEach(chat => {
                        if(chat.id === e.chat.id)
                        {
                            chat.read_at = e.chat.read_at;
                        }
                    })
                });

            Echo.private('Chat.' + this.friend.session.id)
                .listen('BlockEvent', (e) => {
                    this.session.block = e.blocked;
                });

            Echo.private('Chat.' + this.friend.session.id)
                .listenForWhisper('typing', (e) => {
                   this.isTyping = true;
                   setTimeout(() => {
                       this.isTyping = false;
                   },2000);
                });
            this.getPhoto();

        }
    }
</script>

<style>
    body,html{
        height: 100%;
        margin: 0;
        background: #7F7FD5;
        background: -webkit-linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5);
        background: linear-gradient(to right, #91EAE4, #86A8E7, #7F7FD5);
    }

    .chat{
        margin-top: auto;
        margin-bottom: auto;
    }
    .card{
        height: 500px;
        border-radius: 15px !important;
        background-color: rgba(0,0,0,0.4) !important;
    }
    .contacts_body{
        padding:  0.75rem 0 !important;
        overflow-y: auto;
        white-space: nowrap;
    }
    .msg_card_body{
        overflow-y: auto;
    }
    .card-header{
        border-radius: 15px 15px 0 0 !important;
        border-bottom: 0 !important;
    }
    .card-footer{
        border-radius: 0 0 15px 15px !important;
        border-top: 0 !important;
    }
    .container{
        align-content: center;
    }
    .search{
        border-radius: 15px 0 0 15px !important;
        background-color: rgba(0,0,0,0.3) !important;
        border:0 !important;
        color:white !important;
    }
    .search:focus{
        box-shadow:none !important;
        outline:0px !important;
    }
    .type_msg{
        background-color: rgba(0,0,0,0.3) !important;
        border:0 !important;
        color:white !important;
        height: 60px !important;
        overflow-y: auto;
    }
    .type_msg:focus{
        box-shadow:none !important;
        outline:0px !important;
    }
    .attach_btn{
        border-radius: 15px 0 0 15px !important;
        background-color: rgba(0,0,0,0.3) !important;
        border:0 !important;
        color: white !important;
        cursor: pointer;
    }
    .send_btn{
        border-radius: 0 15px 15px 0 !important;
        background-color: rgba(0,0,0,0.3) !important;
        border:0 !important;
        color: white !important;
        cursor: pointer;
    }
    .search_btn{
        border-radius: 0 15px 15px 0 !important;
        background-color: rgba(0,0,0,0.3) !important;
        border:0 !important;
        color: white !important;
        cursor: pointer;
    }
    .contacts{
        list-style: none;
        padding: 0;
    }
    .contacts li{
        width: 100% !important;
        padding: 5px 10px;
        margin-bottom: 15px !important;
    }
    .active{
        background-color: rgba(0,0,0,0.3);
    }
    .user_img{
        height: 70px;
        width: 70px;
        border:1.5px solid #f5f6fa;

    }
    .user_img_msg{
        height: 40px;
        width: 40px;
        border:1.5px solid #f5f6fa;

    }
    .img_cont{
        position: relative;
        height: 70px;
        width: 70px;
    }
    .img_cont_msg{
        height: 40px;
        width: 40px;
    }
    .online_icon{
        position: absolute;
        height: 15px;
        width:15px;
        background-color: #4cd137;
        border-radius: 50%;
        bottom: 0.2em;
        right: 0.4em;
        border:1.5px solid white;
    }
    .offline{
        background-color: #c23616 !important;
    }
    .user_info{
        margin-top: auto;
        margin-bottom: auto;
        margin-left: 15px;
    }
    .user_info span{
        font-size: 20px;
        color: white;
    }
    .user_info p{
        font-size: 10px;
        color: rgba(255,255,255,0.6);
    }
    .video_cam{
        margin-left: 50px;
        margin-top: 5px;
    }
    .video_cam span{
        color: white;
        font-size: 20px;
        cursor: pointer;
        margin-right: 20px;
    }
    .msg_cotainer{
        margin-top: auto;
        margin-bottom: auto;
        margin-left: 10px;
        border-radius: 25px;
        background-color: #82ccdd;
        padding: 10px;
        position: relative;
        min-width: 120px;
    }
    .msg_cotainer_send{
        margin-top: auto;
        margin-bottom: auto;
        margin-right: 10px;
        border-radius: 25px;
        background-color: #78e08f;
        padding: 10px;
        position: relative;
        min-width: 120px;
    }
    .msg_time{
        position: absolute;
        left: 0;
        bottom: -15px;
        color: rgba(255,255,255,0.5);
        font-size: 10px;
    }
    .msg_time_send{
        position: absolute;
        right:0;
        bottom: -15px;
        color: rgba(255,255,255,0.5);
        font-size: 10px;
    }
    .msg_head{
        position: relative;
    }
    #action_menu_btn{
        position: absolute;
        right: 10px;
        top: 10px;
        color: white;
        cursor: pointer;
        font-size: 20px;
    }
    .action_menu{
        z-index: 1;
        position: absolute;
        padding: 15px 0;
        background-color: rgba(0,0,0,0.5);
        color: white;
        border-radius: 15px;
        top: 30px;
        right: 15px;
        display: block;

    }
    .action_menu ul{
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .action_menu ul li{
        width: 100%;
        padding: 10px 15px;
        margin-bottom: 5px;
    }
    .action_menu ul li i{
        padding-right: 10px;

    }
    .action_menu ul li:hover{
        cursor: pointer;
        background-color: rgba(0,0,0,0.2);
    }
    @media(max-width: 576px){
        .contacts_card{
            margin-bottom: 15px !important;
        }
    }

</style>
