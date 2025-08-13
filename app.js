$(document).ready(function() {
    // Load data from localStorage or initial data
    let visitors = JSON.parse(localStorage.getItem('visitors')) || initialVisitors;
    let notifications = JSON.parse(localStorage.getItem('notifications')) || initialNotifications;

    // Normalize visitor data
    visitors = visitors.map(v => ({
        id: v.id || Date.now(),
        name: v.name || "Unknown",
        vehicleInfo: v.vehicleInfo || "N/A",
        accompanyingPersons: v.accompanyingPersons || 0,
        idCard: v.idCard || "N/A",
        phone: v.phone || "N/A",
        host: v.host || "N/A",
        stayStart: v.stayStart || new Date().toISOString().split('T')[0],
        stayEnd: v.stayEnd || new Date().toISOString().split('T')[0],
        reason: v.reason || "N/A",
        checkIn: v.checkIn || null,
        checkOut: v.checkOut || null,
        status: v.status || "Pending",
        badgeId: v.badgeId || `V${Math.floor(100 + Math.random() * 900)}`,
        preRegistered: v.preRegistered || false
    }));

    // Save to localStorage
    function saveData() {
        localStorage.setItem('visitors', JSON.stringify(visitors));
        localStorage.setItem('notifications', JSON.stringify(notifications));
    }

    // Render navigation
    function renderNav(role) {
        const nav = $('#nav-menu');
        nav.empty();
        const tabs = role === 'admin' ? [
            { id: 'dashboard', label: 'Dashboard', icon: 'fas fa-chart-line' },
            { id: 'register', label: 'Register', icon: 'fas fa-user-plus' },
            { id: 'current', label: 'Current Visitors', icon: 'fas fa-users' },
            { id: 'history', label: 'History', icon: 'fas fa-history' },
            { id: 'reports', label: 'Reports', icon: 'fas fa-file-alt' },
            { id: 'notifications', label: 'Notifications', icon: 'fas fa-bell' },
            { id: 'scanning', label: 'Scan Badge', icon: 'fas fa-qrcode' },
            { id: 'preregistration', label: 'Pre-Registration', icon: 'fas fa-clipboard-check' },
            { id: 'analytics', label: 'Analytics', icon: 'fas fa-chart-pie' }
        ] : role === 'gatekeeper' ? [
            { id: 'register', label: 'Register', icon: 'fas fa-user-plus' },
            { id: 'current', label: 'Current Visitors', icon: 'fas fa-users' },
            { id: 'notifications', label: 'Notifications', icon: 'fas fa-bell' },
            { id: 'scanning', label: 'Scan Badge', icon: 'fas fa-qrcode' }
        ] : [
            { id: 'register', label: 'Submit Guest Request', icon: 'fas fa-user-plus' },
            { id: 'current', label: 'Current Visitors', icon: 'fas fa-users' },
            { id: 'notifications', label: 'Notifications', icon: 'fas fa-bell' }
        ];
        tabs.forEach(tab => {
            nav.append(`<button class="px-4 py-2 rounded-lg m-1 ${tab.id === 'dashboard' ? 'bg-gradient-to-r from-indigo-600 to-indigo-800 text-white' : 'bg-white text-indigo-600'} text-base shadow-custom nav-button" data-tab="${tab.id}"><i class="${tab.icon} mr-2"></i>${tab.label}</button>`);
        });
    }

    // Render content
    function renderContent(tab, role) {
        const content = $('#content');
        content.empty();
        if (tab === 'dashboard' && role === 'admin') {
            content.html(`
        <div class="space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
              <h3 class="text-base font-semibold text-indigo-600"><i class="fas fa-users mr-2"></i>Today's Visitors</h3>
              <p class="text-2xl font-bold text-gray-800">${visitors.filter(v => v.checkIn && v.checkIn.startsWith(new Date().toISOString().split('T')[0])).length}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
              <h3 class="text-base font-semibold text-emerald-600"><i class="fas fa-user-check mr-2"></i>Active Visitors</h3>
              <p class="text-2xl font-bold text-gray-800">${visitors.filter(v => v.status === 'Checked In').length}</p>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
              <h3 class="text-base font-semibold text-amber-600"><i class="fas fa-clipboard-check mr-2"></i>Pre-Registered</h3>
              <p class="text-2xl font-bold text-gray-800">${visitors.filter(v => v.preRegistered).length}</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl shadow-custom w-120 h-120">
              <h3 class="text-base font-semibold mb-2 text-indigo-600"><i class="fas fa-chart-bar mr-2"></i>Visitor Purpose Distribution</h3>
              <canvas id="purposeChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-custom w-120 h-120">
              <h3 class="text-base font-semibold mb-2 text-emerald-600"><i class="fas fa-chart-line mr-2"></i>Daily Visitors Trend</h3>
              <canvas id="dailyChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-custom w-120 h-120">
              <h3 class="text-base font-semibold mb-2 text-amber-600"><i class="fas fa-chart-pie mr-2"></i>Visitor Status Breakdown</h3>
              <canvas id="statusChart"></canvas>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-custom w-120 h-120">
              <h3 class="text-base font-semibold mb-2 text-purple-600"><i class="fas fa-user-tie mr-2"></i>Host Distribution</h3>
              <canvas id="hostChart"></canvas>
            </div>
          </div>
        </div>
      `);
            // Visitor Purpose Distribution
            const purposes = visitors.reduce((acc, v) => { acc[v.reason] = (acc[v.reason] || 0) + 1; return acc; }, {});
            new Chart($('#purposeChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(purposes),
                    datasets: [{
                        label: 'Visitor Purpose',
                        data: Object.values(purposes),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderColor: ['#1e3a8a', '#047857', '#b45309', '#b91c1c', '#6d28d9'],
                        borderWidth: 1
                    }]
                },
                options: {
                    aspectRatio: 4/3, // 3:4 ratio (height:width)
                    scales: { y: { beginAtZero: true } },
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            // Daily Visitors Trend
            const dates = [...new Set(visitors.filter(v => v.checkIn).map(v => v.checkIn.split('T')[0]))].sort();
            const visitorCounts = dates.map(date => ({
                date,
                count: visitors.filter(v => v.checkIn && v.checkIn.startsWith(date)).length
            }));
            new Chart($('#dailyChart'), {
                type: 'line',
                data: {
                    labels: visitorCounts.map(v => v.date),
                    datasets: [{
                        label: 'Daily Visitors',
                        data: visitorCounts.map(v => v.count),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    aspectRatio: 4/3, // 3:4 ratio (height:width)
                    scales: { y: { beginAtZero: true } },
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            // Visitor Status Breakdown
            const statuses = visitors.reduce((acc, v) => { acc[v.status] = (acc[v.status] || 0) + 1; return acc; }, {});
            new Chart($('#statusChart'), {
                type: 'pie',
                data: {
                    labels: Object.keys(statuses),
                    datasets: [{
                        label: 'Visitor Status',
                        data: Object.values(statuses),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderColor: ['#1e3a8a', '#047857', '#b45309', '#b91c1c', '#6d28d9'],
                        borderWidth: 1
                    }]
                },
                options: {
                    aspectRatio: 4/3, // 3:4 ratio (height:width)
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            // Host Distribution
            const hostCounts = visitors.reduce((acc, v) => { acc[v.host] = (acc[v.host] || 0) + 1; return acc; }, {});
            new Chart($('#hostChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(hostCounts),
                    datasets: [{
                        label: 'Visitors per Host',
                        data: Object.values(hostCounts),
                        backgroundColor: ['#8b5cf6', '#ec4899', '#14b8a6', '#f97316'],
                        borderColor: ['#6d28d9', '#be185d', '#0f766e', '#c2410c'],
                        borderWidth: 1
                    }]
                },
                options: {
                    aspectRatio: 4/3, // 3:4 ratio (height:width)
                    scales: { y: { beginAtZero: true } },
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        } else if (tab === 'register' && (role === 'admin' || role === 'gatekeeper' || role === 'host')) {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-user-plus mr-2"></i>${role === 'host' ? 'Submit Guest Request' : 'Register New Visitor'}</h2>
          <button id="addVisitor" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform text-base mb-4"><i class="fas fa-plus mr-2"></i>Add Visitor</button>
        </div>
      `);
        } else if (tab === 'current') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-users mr-2"></i>Current Visitors</h2>
          <table id="currentTable" class="w-full table-auto text-base">
            <thead class="bg-indigo-100">
              <tr>
                <th class="p-2"><i class="fas fa-user mr-1"></i>Name</th>
                <th class="p-2 hidden sm:table-cell"><i class="fas fa-id-card mr-1"></i>ID Card</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-phone mr-1"></i>Phone</th>
                <th class="p-2"><i class="fas fa-comment mr-1"></i>Reason</th>
                <th class="p-2 hidden lg:table-cell"><i class="fas fa-user-tie mr-1"></i>Host</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-calendar mr-1"></i>Stay Period</th>
                <th class="p-2"><i class="fas fa-info-circle mr-1"></i>Status</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-qrcode mr-1"></i>Badge</th>
                <th class="p-2"><i class="fas fa-cogs mr-1"></i>Action</th>
                <th class="p-2 hidden sm:table-cell"><i class="fas fa-print mr-1"></i>Pass</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      `);
            $('#currentTable').DataTable({
                data: visitors.filter(v => role === 'host' ? v.status === 'Pending' : v.status === 'Checked In' || v.status === 'Pending' || v.status === 'Approved'),
                columns: [
                    { data: 'name', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'idCard', className: 'table-cell hidden sm:table-cell', render: data => data || 'N/A' },
                    { data: 'phone', className: 'table-cell hidden md:table-cell', render: data => data || 'N/A' },
                    { data: 'reason', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'host', className: 'table-cell hidden lg:table-cell', render: data => data || 'N/A' },
                    { data: null, className: 'table-cell hidden md:table-cell', render: data => `${data.stayStart || 'N/A'} to ${data.stayEnd || 'N/A'}` },
                    { data: 'status', className: 'table-cell', render: data => `<span class="badge text-xs">${data || 'N/A'}</span>` },
                    { data: 'badgeId', className: 'table-cell hidden md:table-cell', render: data => data || 'N/A' },
                    {
                        data: null,
                        className: 'table-cell',
                        render: data => {
                            let buttons = '';
                            if (data.status === 'Pending' && role === 'admin') {
                                buttons += `
                  <button class="approve bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-check mr-2"></i>Approve</button>
                  <button class="reject bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-times mr-2"></i>Reject</button>
                `;
                            } else if (data.status === 'Approved' && role === 'gatekeeper') {
                                buttons += `<button class="issue-pass bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-ticket-alt mr-2"></i>Issue Pass</button>`;
                            } else if (data.status === 'Checked In') {
                                buttons += `<button class="checkout bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-sign-out-alt mr-2"></i>Check Out</button>`;
                            }
                            return buttons;
                        }
                    },
                    {
                        data: null,
                        className: 'table-cell hidden sm:table-cell',
                        render: data => (data.status === 'Approved' || data.status === 'Checked In') ? `<button class="print bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-print mr-2"></i>Print</button>` : ''
                    }
                ],
                responsive: true,
                pageLength: 5
            });
        } else if (tab === 'history' && role === 'admin') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-history mr-2"></i>Visitor History</h2>
          <table id="historyTable" class="w-full table-auto text-base">
            <thead class="bg-indigo-100">
              <tr>
                <th class="p-2"><i class="fas fa-user mr-1"></i>Name</th>
                <th class="p-2 hidden sm:table-cell"><i class="fas fa-id-card mr-1"></i>ID Card</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-phone mr-1"></i>Phone</th>
                <th class="p-2"><i class="fas fa-comment mr-1"></i>Reason</th>
                <th class="p-2 hidden lg:table-cell"><i class="fas fa-user-tie mr-1"></i>Host</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-sign-in-alt mr-1"></i>Check-In</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-sign-out-alt mr-1"></i>Check-Out</th>
                <th class="p-2"><i class="fas fa-info-circle mr-1"></i>Status</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-qrcode mr-1"></i>Badge</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      `);
            $('#historyTable').DataTable({
                data: visitors,
                columns: [
                    { data: 'name', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'idCard', className: 'table-cell hidden sm:table-cell', render: data => data || 'N/A' },
                    { data: 'phone', className: 'table-cell hidden md:table-cell', render: data => data || 'N/A' },
                    { data: 'reason', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'host', className: 'table-cell hidden lg:table-cell', render: data => data || 'N/A' },
                    { data: 'checkIn', className: 'table-cell hidden md:table-cell', render: data => data ? new Date(data).toLocaleString() : 'N/A' },
                    { data: 'checkOut', className: 'table-cell hidden md:table-cell', render: data => data ? new Date(data).toLocaleString() : 'N/A' },
                    { data: 'status', className: 'table-cell', render: data => `<span class="badge text-xs">${data || 'N/A'}</span>` },
                    { data: 'badgeId', className: 'table-cell hidden md:table-cell', render: data => data || 'N/A' }
                ],
                responsive: true,
                pageLength: 5
            });
        } else if (tab === 'reports' && role === 'admin') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-file-alt mr-2"></i>Visitor Reports</h2>
          <select id="reportType" class="p-2 border border-indigo-300 rounded-lg mb-4 focus:ring-2 focus:ring-indigo-600 text-base w-full">
            <option value="all">All Visitors</option>
            <option value="overstayed">Overstayed</option>
            <option value="rejected">Rejected</option>
            <option value="approved">Approved</option>
            <option value="completed">Visit Completed</option>
          </select>
          <div id="reportContent" class="space-y-3"></div>
        </div>
      `);
            $('#reportType').change(function() {
                const type = $(this).val();
                let filteredVisitors = visitors;
                if (type === 'overstayed') {
                    filteredVisitors = visitors.filter(v => v.status === 'Checked In' && new Date(v.stayEnd) < new Date());
                } else if (type === 'rejected') {
                    filteredVisitors = visitors.filter(v => v.status === 'Rejected');
                } else if (type === 'approved') {
                    filteredVisitors = visitors.filter(v => v.status === 'Approved' || v.status === 'Checked In');
                } else if (type === 'completed') {
                    filteredVisitors = visitors.filter(v => v.status === 'Checked Out');
                }
                const total = filteredVisitors.length;
                const reasons = filteredVisitors.reduce((acc, v) => { acc[v.reason] = (acc[v.reason] || 0) + 1; return acc; }, {});
                const hosts = filteredVisitors.reduce((acc, v) => { acc[v.host] = (acc[v.host] || 0) + 1; return acc; }, {});
                $('#reportContent').html(`
          <p class="text-base"><strong><i class="fas fa-users mr-2"></i>Total Visitors:</strong> ${total}</p>
          <div><strong class="text-base"><i class="fas fa-comment mr-2"></i>Reason Breakdown:</strong><ul class="list-disc pl-5 text-base">${Object.entries(reasons).map(([k, v]) => `<li class="text-indigo-800">${k}: ${v}</li>`).join('')}</ul></div>
          <div><strong class="text-base"><i class="fas fa-user-tie mr-2"></i>Host Breakdown:</strong><ul class="list-disc pl-5 text-base">${Object.entries(hosts).map(([k, v]) => `<li class="text-indigo-800">${k}: ${v}</li>`).join('')}</ul></div>
        `);
            }).change();
        } else if (tab === 'notifications') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-bell mr-2"></i>Notifications</h2>
          <table id="notificationsTable" class="w-full table-auto text-base">
            <thead class="bg-indigo-100">
              <tr>
                <th class="p-2"><i class="fas fa-comment mr-1"></i>Message</th>
                <th class="p-2"><i class="fas fa-clock mr-1"></i>Time</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      `);
            $('#notificationsTable').DataTable({
                data: notifications.filter(n => n.role === 'all' || n.role === role),
                columns: [
                    { data: 'message', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'time', className: 'table-cell', render: data => data ? new Date(data).toLocaleString() : 'N/A' }
                ],
                responsive: true,
                pageLength: 5
            });
        } else if (tab === 'scanning' && (role === 'admin' || role === 'gatekeeper')) {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-qrcode mr-2"></i>Scan Visitor Badge</h2>
          <div class="space-y-3">
            <input type="text" id="badgeId" placeholder="Enter Badge ID (e.g., V001)" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base">
            <button id="scan" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform w-full text-base"><i class="fas fa-qrcode mr-2"></i>Scan</button>
            <p id="scanResult" class="text-emerald-600"></p>
          </div>
        </div>
      `);
        } else if (tab === 'preregistration' && role === 'admin') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-clipboard-check mr-2"></i>Pre-Registration Requests</h2>
          <table id="preregTable" class="w-full table-auto text-base">
            <thead class="bg-indigo-100">
              <tr>
                <th class="p-2"><i class="fas fa-user mr-1"></i>Name</th>
                <th class="p-2 hidden sm:table-cell"><i class="fas fa-id-card mr-1"></i>ID Card</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-phone mr-1"></i>Phone</th>
                <th class="p-2"><i class="fas fa-comment mr-1"></i>Reason</th>
                <th class="p-2 hidden lg:table-cell"><i class="fas fa-user-tie mr-1"></i>Host</th>
                <th class="p-2 hidden md:table-cell"><i class="fas fa-calendar mr-1"></i>Stay Period</th>
                <th class="p-2"><i class="fas fa-info-circle mr-1"></i>Status</th>
                <th class="p-2"><i class="fas fa-cogs mr-1"></i>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      `);
            $('#preregTable').DataTable({
                data: visitors.filter(v => v.preRegistered),
                columns: [
                    { data: 'name', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'idCard', className: 'table-cell hidden sm:table-cell', render: data => data || 'N/A' },
                    { data: 'phone', className: 'table-cell hidden md:table-cell', render: data => data || 'N/A' },
                    { data: 'reason', className: 'table-cell', render: data => data || 'N/A' },
                    { data: 'host', className: 'table-cell hidden lg:table-cell', render: data => data || 'N/A' },
                    { data: null, className: 'table-cell hidden md:table-cell', render: data => `${data.stayStart || 'N/A'} to ${data.stayEnd || 'N/A'}` },
                    { data: 'status', className: 'table-cell', render: data => `<span class="badge text-xs">${data || 'N/A'}</span>` },
                    {
                        data: null,
                        className: 'table-cell',
                        render: data => `
              <button class="approve bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-check mr-2"></i>Approve</button>
              <button class="reject bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 hover:scale-105 transition-transform text-base" data-id="${data.id}"><i class="fas fa-times mr-2"></i>Reject</button>
            `
                    }
                ],
                responsive: true,
                pageLength: 5
            });
        } else if (tab === 'analytics' && role === 'admin') {
            content.html(`
        <div class="bg-white p-4 rounded-xl shadow-custom card-hover">
          <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-chart-pie mr-2"></i>Analytics</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="h-64">
              <h3 class="text-base font-semibold mb-2 text-indigo-600"><i class="fas fa-chart-bar mr-2"></i>Visitor Purpose</h3>
              <canvas id="analyticsPurpose"></canvas>
            </div>
            <div class="h-64">
              <h3 class="text-base font-semibold mb-2 text-emerald-600"><i class="fas fa-chart-line mr-2"></i>Visitor Trend</h3>
              <canvas id="analyticsTrend"></canvas>
            </div>
          </div>
        </div>
      `);
            new Chart($('#analyticsPurpose'), {
                type: 'bar',
                data: {
                    labels: Object.keys(visitors.reduce((acc, v) => { acc[v.reason] = (acc[v.reason] || 0) + 1; return acc; }, {})),
                    datasets: [{
                        label: 'Visitor Purpose',
                        data: Object.values(visitors.reduce((acc, v) => { acc[v.reason] = (acc[v.reason] || 0) + 1; return acc; }, {})),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderColor: ['#1e3a8a', '#047857', '#b45309', '#b91c1c', '#6d28d9'],
                        borderWidth: 1,
                        class: ['h-120', 'w-120']
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true } },
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            new Chart($('#analyticsTrend'), {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Daily Visitors',
                        data: visitorCounts.map(v => v.count),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    aspectRatio: 4/3, // 3:4 ratio (height:width)
                    scales: { y: { beginAtZero: true } },
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        }
    }

    // Handle role change
    $('#role').change(function() {
        const role = $(this).val();
        renderNav(role);
        renderContent('dashboard', role);
    });

    // Handle navigation
    $(document).on('click', '.nav-button', function() {
        const tab = $(this).data('tab');
        const role = $('#role').val();
        $('.nav-button').removeClass('bg-gradient-to-r from-indigo-600 to-indigo-800 text-white').addClass('bg-white text-indigo-600');
        $(this).removeClass('bg-white text-indigo-600').addClass('bg-gradient-to-r from-indigo-600 to-indigo-800 text-white');
        renderContent(tab, role);
    });

    // Hamburger menu
    $('#hamburger').click(function() {
        $('#nav-menu').toggleClass('open');
    });

    // Visitor modal
    function openVisitorModal(mode, visitor = {}) {
        $('#modalTitleText').text(mode === 'add' ? 'Add Visitor' : 'Edit Visitor');
        $('#visitorId').val(visitor.id || '');
        $('#name').val(visitor.name || '');
        $('#vehicleInfo').val(visitor.vehicleInfo || '');
        $('#accompanyingPersons').val(visitor.accompanyingPersons || '');
        $('#idCard').val(visitor.idCard || '');
        $('#phone').val(visitor.phone || '');
        $('#host').empty().append('<option value="">Select Host</option>').append(hosts.map(h => `<option value="${h}">${h}</option>`));
        $('#host').val(visitor.host || '');
        $('#hostSearch').val('');
        $('#stayStart').val(visitor.stayStart || '');
        $('#stayEnd').val(visitor.stayEnd || '');
        $('#reason').val(visitor.reason || '');
        $('#visitorModal').removeClass('hidden').addClass('modal-open');
    }

    $(document).on('click', '#addVisitor', function() {
        openVisitorModal('add');
    });

    $('#closeModal').click(function() {
        $('#visitorModal').addClass('hidden').removeClass('modal-open');
    });

    // Host search
    $('#hostSearch').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('#host').empty().append('<option value="">Select Host</option>').append(
            hosts.filter(h => h.toLowerCase().includes(search)).map(h => `<option value="${h}">${h}</option>`)
        );
    });

    // Visitor form submission
    $('#visitorForm').submit(function(e) {
        e.preventDefault();
        const visitor = {
            id: $('#visitorId').val() || Date.now(),
            name: $('#name').val(),
            vehicleInfo: $('#vehicleInfo').val(),
            accompanyingPersons: parseInt($('#accompanyingPersons').val()) || 0,
            idCard: $('#idCard').val(),
            phone: $('#phone').val(),
            host: $('#host').val(),
            stayStart: $('#stayStart').val(),
            stayEnd: $('#stayEnd').val(),
            reason: $('#reason').val(),
            checkIn: null,
            checkOut: null,
            status: 'Pending',
            badgeId: `V${Math.floor(100 + Math.random() * 900)}`,
            preRegistered: $('#role').val() === 'host'
        };
        if ($('#visitorId').val()) {
            visitors = visitors.map(v => v.id == visitor.id ? visitor : v);
        } else {
            visitors.push(visitor);
            notifications.push({
                id: Date.now(),
                message: `${visitor.name} ${$('#role').val() === 'host' ? 'submitted a guest request' : 'registered'}`,
                time: new Date().toISOString(),
                role: $('#role').val() === 'host' ? 'admin' : 'all'
            });
        }
        saveData();
        $('#visitorModal').addClass('hidden').removeClass('modal-open');
        renderContent('current', $('#role').val());
    });

    // Confirm modal
    function openConfirmModal(message, callback) {
        $('#confirmMessage').text(message);
        $('#confirmModal').removeClass('hidden').addClass('modal-open');
        $('#confirmAction').off('click').on('click', function() {
            callback();
            $('#confirmModal').addClass('hidden').removeClass('modal-open');
        });
        $('#cancelAction').off('click').on('click', function() {
            $('#confirmModal').addClass('hidden').removeClass('modal-open');
        });
    }

    // Actions
    $(document).on('click', '.approve', function() {
        const id = $(this).data('id');
        openConfirmModal('Approve this visitor?', () => {
            visitors = visitors.map(v => v.id == id ? { ...v, status: 'Approved' } : v);
            notifications.push({ id: Date.now(), message: `Visitor ${visitors.find(v => v.id == id).name} approved`, time: new Date().toISOString(), role: 'all' });
            saveData();
            renderContent('current', $('#role').val());
        });
    });

    $(document).on('click', '.reject', function() {
        const id = $(this).data('id');
        openConfirmModal('Reject this visitor?', () => {
            visitors = visitors.map(v => v.id == id ? { ...v, status: 'Rejected' } : v);
            notifications.push({ id: Date.now(), message: `Visitor ${visitors.find(v => v.id == id).name} rejected`, time: new Date().toISOString(), role: 'all' });
            saveData();
            renderContent('current', $('#role').val());
        });
    });

    $(document).on('click', '.issue-pass', function() {
        const id = $(this).data('id');
        openConfirmModal('Issue pass for this visitor?', () => {
            visitors = visitors.map(v => v.id == id ? { ...v, status: 'Checked In', checkIn: new Date().toISOString() } : v);
            notifications.push({ id: Date.now(), message: `Visitor ${visitors.find(v => v.id == id).name} checked in`, time: new Date().toISOString(), role: 'all' });
            saveData();
            renderContent('current', $('#role').val());
        });
    });

    $(document).on('click', '.checkout', function() {
        const id = $(this).data('id');
        openConfirmModal('Check out this visitor?', () => {
            visitors = visitors.map(v => v.id == id ? { ...v, status: 'Checked Out', checkOut: new Date().toISOString() } : v);
            notifications.push({ id: Date.now(), message: `Visitor ${visitors.find(v => v.id == id).name} checked out`, time: new Date().toISOString(), role: 'all' });
            saveData();
            renderContent('current', $('#role').val());
        });
    });

    $(document).on('click', '.print', function() {
        const id = $(this).data('id');
        const visitor = visitors.find(v => v.id == id);
        if (visitor.status !== 'Approved' && visitor.status !== 'Checked In') return;
        const card = $(`
      <div class="visitor-card p-4 bg-white text-center">
        <h3 class="text-lg font-semibold text-indigo-600"><i class="fas fa-user mr-2"></i>Visitor Pass</h3>
        <p><strong>Name:</strong> ${visitor.name}</p>
        <p><strong>ID Card:</strong> ${visitor.idCard}</p>
        <p><strong>Host:</strong> ${visitor.host}</p>
        <p><strong>Reason:</strong> ${visitor.reason}</p>
        <p><strong>Badge ID:</strong> ${visitor.badgeId}</p>
        <canvas id="barcode"></canvas>
      </div>
    `);
        $('body').append(card);
        JsBarcode('#barcode', visitor.badgeId, { format: 'CODE128', displayValue: true });
        html2canvas(card[0]).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const win = window.open();
            win.document.write(`
        <html>
          <body>
            <img src="${imgData}" onload="window.print(); window.close();" />
          </body>
        </html>
      `);
            card.remove();
        });
    });

    $(document).on('click', '#scan', function() {
        const badgeId = $('#badgeId').val();
        const visitor = visitors.find(v => v.badgeId === badgeId);
        if (visitor) {
            if (visitor.status === 'Approved') {
                openConfirmModal(`Check in ${visitor.name}?`, () => {
                    visitors = visitors.map(v => v.badgeId === badgeId ? { ...v, status: 'Checked In', checkIn: new Date().toISOString() } : v);
                    notifications.push({ id: Date.now(), message: `${visitor.name} checked in via badge scan`, time: new Date().toISOString(), role: 'all' });
                    saveData();
                    $('#scanResult').text(`Visitor ${visitor.name} checked in successfully.`).removeClass('text-red-600').addClass('text-emerald-600');
                    renderContent('scanning', $('#role').val());
                });
            } else if (visitor.status === 'Checked In') {
                openConfirmModal(`Check out ${visitor.name}?`, () => {
                    visitors = visitors.map(v => v.badgeId === badgeId ? { ...v, status: 'Checked Out', checkOut: new Date().toISOString() } : v);
                    notifications.push({ id: Date.now(), message: `${visitor.name} checked out via badge scan`, time: new Date().toISOString(), role: 'all' });
                    saveData();
                    $('#scanResult').text(`Visitor ${visitor.name} checked out successfully.`).removeClass('text-red-600').addClass('text-emerald-600');
                    renderContent('scanning', $('#role').val());
                });
            } else {
                $('#scanResult').text(`Visitor ${visitor.name} is ${visitor.status.toLowerCase()}.`).addClass('text-red-600').removeClass('text-emerald-600');
            }
        } else {
            $('#scanResult').text('Invalid Badge ID.').addClass('text-red-600').removeClass('text-emerald-600');
        }
    });

    // Initial render
    renderNav('admin');
    renderContent('dashboard', 'admin');
});