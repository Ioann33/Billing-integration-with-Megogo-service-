<template>
    <div id="page">
        <head-bar></head-bar>
        <nav-bar></nav-bar>

        <div class="page-content header-clear-medium">
            <div class="card" v-if="login">
                <div class="content">
                    <div class="font-18 color-black">Логин в Megogo: <b>{{ login }}</b></div>
                    <div style="display: flex; justify-content: space-between" class="pt-2">
                        <div class="font-18 color-black pt-2">Пароль: <b>******</b></div>
                        <a href="#"
                           v-on:click.prevent="changeCredentials"
                           class="btn shadow-bg shadow-bg-m btn-m btn-full mb-3 rounded-s text-uppercase font-900 shadow-s bg-green-dark mt-1">
                            Сменить
                        </a>
                    </div>
                    <p v-if="current_tariff['plan_name']">
                        Ваш активный тариф:  {{current_tariff['plan_name']}}
                    </p>
                </div>
                <div v-if="alertDisConnect">
                    <a href="#"
                       v-if="current_tariff['plan_name']"
                       v-on:click.prevent="choiceDisConnect"
                       class="btn shadow-bg shadow-bg-m btn-m btn-full mb-3 rounded-s text-uppercase font-900 shadow-s bg-red-dark mt-1"
                    >

                        Отменить подписку

                    </a>
                </div>
                <div v-else class="p-2 alert-warning">
                    <p class="alert-danger">Доступное количество отключений на месяц: {{current_tariff['prolong_time']}}</p>
                    <p>Вы уверенны что хотите отключить текущий тариф?</p>
                    <div style="display: flex; justify-content: space-around">

                        <button
                            class="btn btn-success"
                            v-on:click="disconnectService"
                            v-if="current_tariff['prolong_time']>0"
                        >

                            Подтвердить
                        </button>

                        <div class="alert-warning text-center" v-else>
                            Вы исчерпали разрешенное количество отключений на месяц
                        </div>
                        <button class="btn btn-danger " v-on:click="cancel(2)">Отменить</button>
                    </div>
                </div>

            </div>
            <div class="card card-style bg-red-dark" v-if="alertError">
                <div class="content">
                    <h4 class="color-white">{{ alertError }}</h4>
                </div>
            </div>
            <div v-if="alertConnect">
                <a href="#" v-on:click.prevent="choiceService(t.id, t.price, t.name)" class="card card-style" v-for="t in tariff_plans">
                    <div class="card mb-0" data-card-height="155" style="background-image:url(images/iptv.jpeg)">
                        <div class="card-top m-2">
                            <p class="px-3 py-1 color-black rounded-s text-uppercase font-700 bg-white float-end font-15"> {{t.price}} ₴</p>
                        </div>
                        <div class="card-bottom px-3 py-2">
                            <h1 class="color-white font-28 pb-1">{{ t.name }}</h1>
                            <p class="color-white opacity-50 mb-2">
                                {{ t.description }}
                            </p>
                        </div>
                        <div class="card-overlay bg-gradient"></div>
                    </div>
                </a>
            </div>
            <div class="alert-warning text-center p-2 h-25 font-14" v-else>
                <div class="alert-info" v-if="current_tariff['plan_name']">
                    Ваша текущая подписка : "{{ current_tariff['plan_name']}}", хотите сменить на "{{ name }} ?"
                </div>
                <p class="alert-danger">Доступное количество переплодключений на месяц: {{current_tariff['prolong_time']}}</p>
                Подтвердить подключение подписки: "{{name}}".  Ежемесячная стоимость:  {{ price }} грн.
                <p>До конца текущего месяца с вас будет списано: {{ diffPrice }} грн</p>

                <div style="display: flex; justify-content: space-around">
                    <div v-if="stateBalance">
                        <button class="btn btn-success"
                                v-on:click="connectService"
                                v-if="current_tariff['prolong_time']>0 || !current_tariff['plan_name'] "
                        >
                            Подтвердить

                        </button>

                        <div class="alert-warning text-center" v-else>
                            Вы исчерпали разрешенное количество переподключений на месяц
                        </div>
                    </div>
                    <div v-else>
                        <p class="alert-danger">Недостаточно средств на счету</p>
                    </div>
                    <button class="btn btn-danger " v-on:click="cancel(1)">Отменить</button>
                </div>

            </div>

        <nav-bar-menu></nav-bar-menu>
    </div>

    </div>
</template>

<script>
import headBar from "../components/headBar";
import navBar from "../components/navBar";
import navBarMenu from "../components/navBarMenu";
import footerAds from "../components/footerAds";

export default {
    name: "Iptv",
    components:{
        navBar, navBarMenu,
        headBar, footerAds
    },

    data(){
        return {
            login: null,
            current_tariff : [],
            tariff_plans: [],
            alertConnect: true,
            alertDisConnect: true,
            alertError : false,
            name: null,
            price: null,
            diffPrice:null,
            service_id: null,
            stateBalance: null,
        }
    },
    methods : {
        getUserInfo(){
            axios.get('/api/getUserInfo')
                .then(response => {

                    console.log(response.data.login)
                    if (response.data.plan_name){
                        this.current_tariff = response.data
                    }

                    if (response.data.login){
                        this.login = response.data.login
                        console.log(response.data['prolong_time'])
                        this.current_tariff['prolong_time'] = response.data['prolong_time']
                    }else {
                        this.current_tariff['prolong_time'] = 1;
                    }

                })
                .catch(er => {
                    console.log(er.status)
                })
        },
        getTariffPlans(){
            axios.get('/api/getTariffPlans')
                .then(res => {
                    console.log(res)
                    this.tariff_plans = res.data;
                })
        },
        choiceService(service_id, price, name){
            axios.get(`api/calculateCost?service_id=${service_id}`)
                .then(res =>{
                    this.diffPrice = res.data.cost
                    this.stateBalance = res.data.stateBalance
                })
            console.log(service_id+price+name)
            this.service_id = service_id
            this.name = name;
            this.price = price
            this.alertConnect = false;

        },
        choiceDisConnect(){
            this.alertDisConnect = false;
        },
        cancel(window){
            if (window === 1){
                this.alertConnect = true;
            }if(window === 2){
                this.alertDisConnect = true
            }

        },
        disconnectService(){
            axios.get(`api/disConnectService?service_id=${this.current_tariff['plan_id']}`)
                .then(res => {
                    console.log('disconnect data'+res.data)
                    if (res.status === 200){
                        this.current_tariff = [];
                        this.current_tariff['prolong_time'] = res.data
                        this.alertDisConnect = true;
                        this.service_id = null;
                    }
                })
                .catch(err => {
                    if (err.response.status === 500){
                        console.error('error status: '+ err.response.status+'. Сервервис временно недоступен, попробуйте позже или обратитесь в службу поддержки')
                        this.alertConnect = true;
                        this.alertError = 'Сервервис временно недоступен, попробуйте позже или обратитесь в службу поддержки.'
                    }
                })
        },
        connectService(){
            if (this.current_tariff['plan_name']){
                console.log(this.current_tariff)
                console.log('turn off current')
                axios.get(`api/disConnectService?service_id=${this.current_tariff['plan_id']}`)
                    .then(res => {
                        console.log('status unsubscribe '+res.status)
                    })
                    .catch(err => {
                        if (err.response.status === 400){
                            console.log(err.response.status+'dfgergerv')
                            this.alertConnect = true;
                            this.alertError = 'Авторизируйтесь на сервесе.'
                        }
                        if (err.response.status === 500){
                            console.log(err.response.status+'dfgergerv')
                            this.alertConnect = true;
                            this.alertError = 'Сервервис временно недоступен, попробуйте позже или обратитесь в службу поддержки.'
                        }
                    })
            }
            console.log('turn on new tariff')
            axios.get(`api/connectService?service_id=${this.service_id}`)
                .then(res => {
                    if (res.status === 200){
                        console.log(res.data)
                        this.current_tariff['plan_name'] = res.data.name
                        this.current_tariff['plan_id'] = res.data.service_id
                        this.current_tariff['prolong_time'] = res.data.prolong_time
                        this.alertConnect = true;
                        this.alertError = false;
                    }
                })
                .catch(err => {
                    if (err.response.status === 400){
                        console.log('return to login')
                        localStorage.setItem('service_id', this.service_id)

                        this.$router.push({name: 'pass'})
                    }
                    if (err.response.status === 402){
                        console.log(err.response.status+'dfgergerv')
                        this.current_tariff['plan_name'] = null
                        this.alertConnect = true;
                        this.alertError = 'Недостаточно средств на счету, пополните ваш баланс.'
                    }
                    if (err.response.status === 500){
                        console.log(err.response.status+'dfgergerv')
                        this.alertConnect = true;
                        this.alertError = 'Сервервис временно недоступен, попробуйте позже или обратитесь в службу поддержки.'
                    }
                })
        },
        changeCredentials(){
            localStorage.setItem('email', this.login)
            this.$router.push({name: 'changePass'})
        }
    },
    updated() {
        update_template()
    },
    mounted() {
        this.getTariffPlans()
        this.getUserInfo()
    }
}
</script>

