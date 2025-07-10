@extends('admin.layout')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/circular-std/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/libs/css/style.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/fontawesome/css/fontawesome-all.css') }}"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/vector-map/jqvmap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/jvectormap/jquery-jvectormap-2.0.2.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/fonts/flag-icon-css/flag-icon.min.css') }}">
@endpush
@section('content')
    <!-- ============================================================== -->
    <!-- wrapper  -->
    <!-- ============================================================== -->
    <div class="dashboard-wrapper">
        <div class="container-fluid  dashboard-content">
            <!-- ============================================================== -->
            <!-- pagehader  -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="page-header">
                        <h3 class="mb-2">Dashboard </h3>
                        {{-- <p class="pageheader-text">Lorem ipsum dolor sit ametllam fermentum ipsum eu porta consectetur adipiscing elit.Nullam vehicula nulla ut egestas rhoncus.</p> --}}
                        <div class="page-breadcrumb">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active"><a href="#"
                                            class="breadcrumb-link">Dashboard</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- pagehader  -->
            <!-- ============================================================== -->
            <div class="row">
                <!-- metric -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Customers</h5>
                            <div class="metric-value d-inline-block">
                                <h1 class="mb-1 text-primary"><span class="abbNum">{{ $custs }}</span></h1>
                            </div>
                            {{-- <div class="metric-label d-inline-block float-right text-success">
                                <i class="fa fa-fw fa-caret-up"></i><span>5.27%</span>
                            </div> --}}
                        </div>
                        <div id="sparkline-1"></div>
                    </div>
                </div>
                <!-- /. metric -->
                <!-- metric -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Total Transfer</h5>
                            <div class="metric-value d-inline-block">
                                <h1 class="mb-1 text-primary">N<span class="abbNum">{{ $tot_trf }}</span></h1>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /. metric -->
                <!-- metric -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Total Deposit</h5>
                            <div class="metric-value d-inline-block">
                                <h1 class="mb-1 text-primary">N<span class="abbNum">{{ $tot_dpst }}</span></h1>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /. metric -->
                <!-- metric -->
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-muted">Total Utility</h5>
                            <div class="metric-value d-inline-block">
                                <h1 class="mb-1 text-primary">N<span class="abbNum">{{ $tot_vtu }}</span></h1>
                            </div>
                        </div>
                        <div id="sparkline-4"></div>
                    </div>
                </div>
                <!-- /. metric -->
            </div>
            <!-- ============================================================== -->
            <!-- revenue  -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-xl-8 col-lg-12 col-md-8 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">Transactions Analysis</h5>
                        <div class="card-body">
                            <canvas id="revenue" width="400" height="150"></canvas>
                        </div>
                        <div class="card-body border-top">
                            <div class="row">
                                <div class="offset-xl-1 col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 p-3">
                                    <h4> Today's transactions:</h4>
                                </div>
                                <div class="offset-xl-1 col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 p-3">
                                    <h2 class="font-weight-normal mb-3">N<span class="abbNum">{{ $tdy_dpst }}</span> </h2>
                                    <div class="text-muted mb-0 mt-3 legend-item">
                                        <span class="fa-xs text-primary mr-1 legend-title "><i
                                                class="fa fa-fw fa-square-full"></i></span>
                                        <span class="legend-text">Deposits</span>
                                    </div>
                                </div>
                                <div class="offset-xl-1 col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 p-3">
                                    <h2 class="font-weight-normal mb-3">N<span class="abbNum">{{ $tdy_trf }}</span></h2>
                                    <div class="text-muted mb-0 mt-3 legend-item">
                                        <span class="fa-xs text-secondary mr-1 legend-title">
                                            <i class="fa fa-fw fa-square-full"></i>
                                        </span>
                                        <span class="legend-text">Transfers</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end reveune  -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- total sale  -->
                <!-- ============================================================== -->
                <div class="col-xl-4 col-lg-12 col-md-4 col-sm-12 col-12">
                    <div class="card">
                        <h5 class="card-header">Total Sale</h5>
                        <div class="card-body">
                            <canvas id="chartjs_doughnut"></canvas>
                            <div class="chart-widget-list">
                                @foreach ($vtanalysis as $vt)
                                    <p>
                                        <span class="legend-text">{{ $vt->type }}</span>
                                        <span class="float-right">N<span class="abbNum">{{ $vt->amount }}</span></span>
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end total sale  -->
                <!-- ============================================================== -->
            </div>
            <div class="row">
                <!-- ============================================================== -->
                <!-- data table  -->
                <!-- ============================================================== -->
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Transactions</h5>
                            <!--<p>Users with most referrals.</p>-->
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                @php
                                    $debitTypeArray = [
                                        'Transfer', 'Airtime', 'Data', 'Electricity', 
                                        'Card Creation', 'Card Funding', 'Cable TV', 
                                        'Betting', 'Gift Card', 'WAEC Result Checker PIN', 
                                        'Jamb', 'WAEC Registration PIN'
                                    ];
                                    
                                    $creditTypeArray = ['Deposit', 'Top-up', 'ATC', 'Sell Gift Card', 'Referral Bonus'];
                                @endphp
                                    
                                <table id="example" class="table table-striped table-bordered second" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th> 
                                            <th>Transaction ID</th>
                                            <th>Type</th>
                                            <th>Direction</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Balance Before</th>
                                            <th>Balance After</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentTransactions as $k => $ls) 
                                           @php
                                                $fname = empty($ls->user->first_name) ? "" : $ls->user->first_name;
                                                $sname = empty($ls->user->surname) ? "" : $ls->user->surname;
                                                $direction = in_array($ls->type, $debitTypeArray) ? 'Debit' : 
                                                            (in_array($ls->type, $creditTypeArray) ? 'Credit' : 'Unknown');
                                                
                                                $data = json_decode($ls->data ?? '{}');
                                                $balance_before = property_exists($data, 'balance_before') ? $data->balance_before : null;
                                                $balance_after = property_exists($data, 'balance_after') ? $data->balance_after : null;
                                            @endphp
                                            <tr>
                                                <td>{{ $k + 1 }}</td>
                                                <td>{{ ucfirst($fname . ' ' . $sname) }}</td>
                                                <td>
                                                    @if(in_array(strtolower($ls->type), ['deposit', 'transfer']))
                                                        <a href="#"
                                                           class="text-primary transaction-link"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#transactionModal"
                                                           data-type="{{ $ls->type }}"
                                                           data-json="{{ htmlentities($ls->data) }}"
                                                           data-ssid="{{$ls->recipient}}"
                                                        >
                                                            {{ $ls->transaction_id }}
                                                        </a>
                                                    @else
                                                        {{ $ls->transaction_id }}
                                                    @endif
                                                </td>
                                                <td>{{ $ls->type }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $direction == 'Credit' ? 'success' : ($direction == 'Debit' ? 'danger' : 'secondary') }}">
                                                        {{ $direction }}
                                                    </span>
                                                </td>
                                                <td>{{ $ls->status }}</td>
                                                <td>
                                                    @if($ls->type == 'Top-up')
                                                        <small>{{$data->purpose ?? ''}}</small>
                                                    @endif
                                                    <b>N</b> {{ number_format($ls->amount, 2) }}
                                                </td>
                                                <td><b>N</b> {{ $balance_before !== null ? number_format($balance_before, 2) : '' }}</td>
                                                <td><b>N</b> {{ $balance_after !== null ? number_format($balance_after, 2) : '' }}</td>
                                                <td>{{ $ls->created_at }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th> 
                                            <th>Transaction ID</th>
                                            <th>Type</th>
                                            <th>Direction</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </tfoot>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end data table  -->
                <!-- ============================================================== -->
            </div>
        </div>
        
        
        <!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody id="transactionDetailsBody">
            <!-- Dynamic rows will be injected here -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

        
        
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <div class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                        Copyright © 2018 Concept. All rights reserved. Dashboard by <a
                            href="https://colorlib.com/wp/">Colorlib</a>.
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                        <div class="text-md-right footer-links d-none d-sm-block">
                            <a href="javascript: void(0);">About</a>
                            <a href="javascript: void(0);">Support</a>
                            <a href="javascript: void(0);">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- end footer -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- end wrapper  -->
    <!-- ============================================================== -->
@endsection
@push('scripts')
    <script src="{{ asset('assets/admin/vendor/jquery/jquery-3.3.1.min.js') }}"></script>
    <!-- bootstrap bundle js-->
    <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <!-- slimscroll js-->
    <script src="{{ asset('assets/admin/vendor/slimscroll/jquery.slimscroll.js') }}"></script>
    <!-- chartjs js-->
    <script src="{{ asset('assets/admin/vendor/charts/charts-bundle/Chart.bundle.js') }}"></script>

    <!-- main js-->
    <script src="{{ asset('assets/admin/libs/js/main-js.js') }}"></script>
    <!-- jvactormap js-->
    <script src="{{ asset('assets/admin/vendor/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/datatables/js/data-table.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.0.4/js/dataTables.rowGroup.min.js"></script>
<script src="https://cdn.datatables.net/select/1.2.7/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>

    <script>
        function abbreviateNumber(value) {
            if (value < 999) {return value}
            let newValue = value;
            const suffixes = ["", "K", "M", "B","T"];
            let suffixNum = 0;
            while (newValue >= 1000) {
                newValue /= 1000;
                suffixNum++;
            }
            newValue = newValue.toPrecision(3);
            newValue += suffixes[suffixNum];
            return newValue;
        }
    </script>
    <script>
        $(document).ready(function() {

            var data = {!! json_encode(collect($analysis)) !!};

            var month = data.map(function (subArray) {
                return subArray.month;
            });

            var deposit = data.map(function (subArray) {
                return subArray.deposit;
            });

            var transfer = data.map(function (subArray) {
                return subArray.transfer;
            });


            var ctx = document.getElementById('revenue').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'line',

                data: {
                    labels: month,
                    datasets: [{
                        label: 'Deposits',
                        data: deposit,
                        backgroundColor: "rgba(89, 105, 255,0.5)",
                        borderColor: "rgba(89, 105, 255,0.7)",
                        borderWidth: 2

                    }, {
                        label: 'Transfers',
                        data: transfer,
                        backgroundColor: "rgba(255, 64, 123,0.5)",
                        borderColor: "rgba(255, 64, 123,0.7)",
                        borderWidth: 2
                    }]
                },
                options: {

                    legend: {
                        display: true,
                        position: 'bottom',

                        labels: {
                            fontColor: '#71748d',
                            fontFamily: 'Circular Std Book',
                            fontSize: 14,
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function(value, index, values) {
                                    return '$' + value;
                                }
                            }
                        }]
                    },


                    scales: {
                        xAxes: [{
                            ticks: {
                                fontSize: 14,
                                fontFamily: 'Circular Std Book',
                                fontColor: '#71748d',
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                fontSize: 14,
                                fontFamily: 'Circular Std Book',
                                fontColor: '#71748d',
                            }
                        }]
                    }

                }
            });
        });
    </script>
    <script>
        if ($('#chartjs_doughnut').length) {
                var vtpass = {!! json_encode(collect($vtanalysis)) !!};
                var type = vtpass.map(function (subArray) {
                    return subArray.type;
                });

                var vtdata = vtpass.map(function (subArray) {
                    return subArray.amount;
                });

                // console.log(type,vtdata);

                var ctx = document.getElementById("chartjs_doughnut").getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: type,
                        datasets: [{
                            backgroundColor: [
                                "#5969ff",
                                "#ff407b",
                                "#25d5f2",
                                "#ffc750",
                            ],
                            data: vtdata
                        }]
                    },
                    options: {

                             legend: {
                        display: true,
                        position: 'bottom',

                        labels: {
                            fontColor: '#71748d',
                            fontFamily: 'Circular Std Book',
                            fontSize: 14,
                        }
                    },

                    
                }

                });
            }
    </script>
    <script>
        $(document).ready(function () {
            $(".abbNum").each(function() {
                let $txt = $(this).text()
                $(this).css("cursor","pointer")
                let kk = abbreviateNumber($txt);
                $(this).text(kk)
                $(this).attr('title',$txt)
            });
        })
    </script>
    <script>
  document.addEventListener('DOMContentLoaded', function () {
    const modalBody = document.getElementById('transactionDetailsBody');

    document.querySelectorAll('.transaction-link').forEach(link => {
      link.addEventListener('click', function () {
        const type = this.getAttribute('data-type');
        const rawJson = this.getAttribute('data-json').replace(/&quot;/g, '"'); 
        const sessionId = this.getAttribute('data-ssid');
        let data;

        try {
          data = JSON.parse(rawJson);
        } catch (e) {
          modalBody.innerHTML = '<tr><td colspan="2">Error parsing transaction data.</td></tr>';
          return;
        }

        let rows = '';

        if (type.toLowerCase() === 'transfer') {
          rows += `<tr><th>Account Name</th><td>${data.account_name || '-'}</td></tr>`;
          rows += `<tr><th>Account Number</th><td>${data.account_number || '-'}</td></tr>`;
          rows += `<tr><th>Bank</th><td>${data.bank || '-'}</td></tr>`;
          rows += `<tr><th>Recipient</th><td>${data.recipient || '-'}</td></tr>`;
          rows += `<tr><th>Amount</th><td>N${Number(data.amount).toFixed(2)}</td></tr>`;
          rows += `<tr><th>Fee</th><td>N${Number(data.fee || 0).toFixed(2)}</td></tr>`;
          rows += `<tr><th>Status</th><td>${data.status || '-'}</td></tr>`;
          rows += `<tr><th>Reference</th><td>${data.reference || data.transaction_id}</td></tr>`;
          rows += `<tr><th>Message</th><td>${data.message || '-'}</td></tr>`;
        } else if (type.toLowerCase() === 'deposit') {
          const auth = data.authorization ? JSON.parse(data.authorization) : {};
          rows += `<tr><th>Source Name</th><td>${auth.sourceAccountName || '-'}</td></tr>`;
          rows += `<tr><th>Source Number</th><td>${auth.sourceAccountNumber || '-'}</td></tr>`;
          rows += `<tr><th>Source Bank</th><td>${auth.sourceBankName || '-'}</td></tr>`;
          rows += `<tr><th>Amount</th><td>N${Number(data.amount).toFixed(2)}</td></tr>`;
          rows += `<tr><th>Fee</th><td>N${Number(data.fee || 0).toFixed(2)}</td></tr>`;
          rows += `<tr><th>Reference</th><td>${data.reference || '-'}</td></tr>`;
          rows += `<tr><th>Status</th><td>${data.status || '-'}</td></tr>`;
          rows += `<tr><th>Message</th><td>${data.message || '-'}</td></tr>`;
          rows += `<tr><th>Session ID</th><td>${sessionId || '-'}</td></tr>`;
        }

        modalBody.innerHTML = rows;
      });
    });
  });
</script>


@endpush
