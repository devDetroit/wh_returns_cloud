@extends('bootstrap.layouts.layout')

@section('content')

<div id="dashboard">
    <div class="row mt-4">
        <div class="col-md-6">
            <h4 class="m-4">General Summary</h4>
            <canvas id="myChart" height="105"></canvas>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="m-4">Daily Summary - @{{ filterDate }}</h5>

                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="dateFilter" class="form-label">Filter by date</label>
                        <input type="date" class="form-control form-control-sm" id="dateFilter" v-model="filterDate">
                    </div>
                </div>
            </div>
            <canvas id="myChart2" height="105"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">User</th>
                        <th scope="col">Total Registred</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in general" :key="index">
                        <th scope="row">@{{ index + 1 }}</th>
                        <td>@{{item.complete_name}}</td>
                        <td>@{{item.totalQuantity}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">User</th>
                        <th scope="col">Total Registred</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in daily" :key="index">
                        <th scope="row">@{{ index + 1 }}</th>
                        <td>@{{item.complete_name}}</td>
                        <td>@{{item.totalQuantity}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="m-4">Summary By Hour</h4>
            <canvas id="myChart3" height="105"></canvas>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">user</th>
                        <th scope="col" v-for="value in hours" :key="index">@{{value}}</th>

                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in hoursDetail" :key="index">
                        <th scope="row">@{{ index + 1 }}</th>
                        <td v-for="hours in item">@{{hours}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>
    const app = new Vue({
        el: '#dashboard',
        data: {
            general: [],
            daily: [],
            dailyByHour: [],
            hours: [],
            hoursDetail: [],
            filterDate: new Date().toLocaleDateString(),
            generalGraph: null,
            dailyGraph: null,
            dailyGraphByHour: null,
        },
        methods: {
            initializeData() {
                let ins = this
                this.general = [];
                this.daily = [];
                this.dailyByHour = [];

                axios({
                        method: 'get',
                        url: 'api/elpDashboard',
                        params: {
                            date: ins.filterDate
                        }
                    })
                    .then(function(response) {
                        ins.general = response.data.generalSummary;
                        ins.daily = response.data.dailySummary;
                        ins.dailySummaryByHour = response.data.dailySummaryByHour;
                        ins.initializeGraphics(response.data.generalSummary);
                        ins.initializeDailyGraphics(response.data.dailySummary);
                        ins.initializeDailyByHoursGraphics(response.data.dailySummaryByHour);
                    });
            },
            initializeGraphics(data) {
                const ctx = document.getElementById('myChart');
                this.generalGraph = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        datasets: [{
                            label: 'Records per User',
                            data: data,
                            backgroundColor: 'rgba(0, 81, 251, 0.6)'
                        }, ]
                    },
                    options: {
                        parsing: {
                            xAxisKey: 'complete_name',
                            yAxisKey: 'totalQuantity'
                        }
                    }
                });
            },
            initializeDailyGraphics(data) {
                const ctx = document.getElementById('myChart2');
                this.dailyGraph = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        datasets: [{
                            label: 'Records daily per User',
                            data: data,
                            backgroundColor: 'rgba(251, 118, 0, 0.42)'
                        }, ]
                    },
                    options: {
                        parsing: {
                            xAxisKey: 'complete_name',
                            yAxisKey: 'totalQuantity'
                        }
                    }
                });
            },
            initializeDailyByHoursGraphics(data) {
                const ctx = document.getElementById('myChart3');
                var hours = [...new Set(data.map(item => item.byhour))];
                this.hours = hours;
                var users = [...new Set(data.map(item => item.complete_name))];
                var newDataSets = []
                this.hoursDetail = []
                for (const value of users) {

                    var totales = [];
                    var totalesDetails = [];
                    totalesDetails.push(value)

                    for (let index = 0; index < hours.length; index++) {
                        var result = data.find((element) => element.byhour == hours[index] && element.complete_name == value)
                        totales.push(result?.totalQuantity ?? 0);
                        totalesDetails.push(result?.totalQuantity ?? 0)
                    }

                    newDataSets.push({
                        label: value,
                        data: totales,
                        backgroundColor: generarColorAleatorio()
                    })

                    this.hoursDetail.push(totalesDetails)
                }
                this.dailyGraphByHour = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: hours,
                        datasets: newDataSets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            },
        },
        mounted() {
            this.initializeData();
        },
        watch: {
            filterDate: function() {
                if (this.filterDate != null) {
                    if (this.generalGraph != null)
                        this.generalGraph.destroy();

                    if (this.dailyGraph != null)
                        this.dailyGraph.destroy();

                    if (this.dailyGraphByHour != null)
                        this.dailyGraphByHour.destroy();
                    
                    this.initializeData();
                }
            }
        }
    })

    function generarColorAleatorio() {
        let colores = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += colores[Math.floor(Math.random() * 16)];
        }
        return color;
    }
</script>
@endsection