<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factory Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .sidebar {
            transition: transform 0.3s;
        }

        .sidebar a:hover {
            background-color: #4a5568;
            border-radius: 0.25rem;
            color: rgba(0,185,185,255);
        }

        .sidebar.active {
            transform: translateX(0%);
        }

        .sidebar.inactive {
            transform: translateX(-100%);
        }

        .shifted {
            margin-left: 16rem;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 5px 0;
            z-index: 1000;
        }

        body {
            margin: 0;
            padding-bottom: 50px;
            box-sizing: border-box;
        }

        .table-header {
            background-color: #0D475D;
            color: white;
        }

        .action-button-accept {
            background-color: #38A169;
            color: white;
        }

        .action-button-pending {
            background-color: #DD6B20;
            color: white;
        }

        .action-button-reject {
            background-color: #E53E3E;
            color: white;
        }

        .action-button {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: bold;
            margin-right: 0.25rem;
        }
    </style>
</head>

<body class="font-sans bg-gray-100 text-gray-800">

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar inactive fixed top-0 left-0 w-64 h-full bg-gray-900 text-white p-4 overflow-y-auto shadow-md">
        <a href="#" class="block mb-3 text-xl font-bold" onclick="toggleSidebar()">✕ Close</a>
        <a href="#machines" onclick="showSection(event, 'machines')" class="block p-3 mb-2 hover:bg-gray-700 rounded">Your Machines</a>
        <a href="#workloads" onclick="showSection(event, 'workloads')" class="block p-3 mb-2 hover:bg-gray-700 rounded">Your Workloads</a>
    </div>

    <!-- Header -->
    <nav class="bg-gray-900 text-white p-4 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <button class="bg-gray-800 p-2 rounded-md hover:bg-gray-700" onclick="toggleSidebar()">Menu ☰</button>
            <img src="{{ asset('frontend/assets/images/auth-login-dark.png') }}" alt="Company Logo" class="h-8">
        </div>
        <div class="flex items-center space-x-4">
            <span>Factory: <strong>{{ Auth::user()->email }}</strong></span>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <button type="button" onclick="confirmLogout()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Logout</button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="mainContent" class="p-6 mt-6 transition-all">

        <!-- Machines Section -->
        <section id="machines" class="mt-6">
            <h2 class="text-2xl font-semibold mb-4">Your Machines</h2>
            @if(session('success'))
                <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4" id="success-alert">
                    {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4" id="error-alert">
                    {{ session('error') }}
                </div>
            @endif

            @if($factory->machines->isEmpty())
                <p class="text-gray-500">You have no machines assigned.</p>
            @else
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md mb-6 display" id="machinesTable">
                    <thead class="table-header">
                        <tr>
                            <th class="py-3 px-4 text-center">ID</th>
                            <th class="py-3 px-4 text-center">Name</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factory->machines as $machine)
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-2 px-4 text-center">{{ $machine->id }}</td>
                                <td class="py-2 px-4 text-center">{{ $machine->name }}</td>
                                <td class="py-2 px-4 text-center">{{ $machine->status }}</td>
                                <td class="py-2 px-4 text-center">
                                    @if($machine->status !== 'Maintenance')
                                        <form action="{{ route('factory.machine.maintenance', $machine->id) }}" method="POST" class="inline maintenance-form">
                                            @csrf
                                            <button type="submit" class="action-button action-button-pending">Maintenance</button>
                                        </form>
                                    @else
                                        <form action="{{ route('factory.machine.available', $machine->id) }}" method="POST" class="inline available-form">
                                            @csrf
                                            <button type="submit" class="action-button action-button-accept">Available</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <!-- Workloads Section -->
        <section id="workloads" class="mt-6" style="display: none;">
            <h2 class="text-2xl font-semibold mb-4">Your Workloads</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md display" id="workloadsTable">
                <thead class="table-header">
                    <tr>
                        <th class="py-3 px-4 text-center">ID</th>
                        <th class="py-3 px-4 text-center">Request ID</th>
                        <th class="py-3 px-4 text-center">Machine ID</th>
                        <th class="py-3 px-4 text-center">Start Date</th>
                        <th class="py-3 px-4 text-center">Completion Date</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Supervisor Approval</th>
                        <th class="py-3 px-4 text-center">Created At</th>
                        <th class="py-3 px-4 text-center">Updated At</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($workloads->isEmpty())
                        <tr>
                            <td colspan="10" class="text-center py-4">No workloads available.</td>
                        </tr>
                    @else
                        @foreach($workloads as $workload)
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-2 px-4 text-center">{{ $workload->id }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->request_id }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->machine_id ?? 'N/A' }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->start_date ? $workload->start_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->completion_date ? $workload->completion_date->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->status }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->supervisor_approval }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-2 px-4 text-center">{{ $workload->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="py-2 px-4 text-center">
                                    @if($workload->status !== 'Working' && $workload->status !== 'Completed')
                                        <button type="button" class="action-button action-button-accept" data-workload-id="{{ $workload->id }}" onclick="openAcceptModal(this)">Accept</button>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer bg-gray-900 text-white text-center py-4">
        <p>&copy; 2024 PT. My Spare Parts. <br> All Rights Reserved. </p>
    </footer>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            sidebar.classList.toggle('inactive');
            mainContent.classList.toggle('shifted');
        }

        function showSection(event, sectionId) {
            event.preventDefault();
            document.querySelectorAll('section').forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
            toggleSidebar();
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure you want to logout?',
                text: "You can cancel if you change your mind.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        $(document).ready(function() {
            $('#machinesTable, #workloadsTable').DataTable({
                "paging": true,
                "pageLength": 10,
                "autoWidth": false
            });
        });
    </script>
</body>

</html>
