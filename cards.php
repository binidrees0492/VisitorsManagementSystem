<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management System</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.22.9/babel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a, #2563eb); }
        .card-hover:hover { transform: translateY(-2px); transition: transform 0.2s; }
        .badge { background-color: #10b981; color: white; padding: 2px 8px; border-radius: 12px; }
        .visitor-card { width: 100%; max-width: 300px; height: 150px; border: 2px solid #1e3a8a; border-radius: 8px; }
        .shadow-custom { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .nav-button { transition: transform 0.2s, background-color 0.2s; }
        .nav-button:hover { transform: scale(1.05); }
        @media print {
            body * { visibility: hidden; }
            .visitor-card, .visitor-card * { visibility: visible; }
            .visitor-card { position: absolute; left: 0; top: 0; }
        }
        .hamburger { display: none; }
        @media (max-width: 640px) {
            .hamburger { display: block; }
            .nav-menu { display: none; }
            .nav-menu.open { display: flex; flex-direction: column; position: absolute; top: 4rem; left: 0; right: 0; background: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 10; }
            .nav-menu.open button { margin: 0.5rem; }
            .table-cell { max-width: 100px; }
        }
        .table-cell { max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
<div id="root"></div>
<script type="text/babel">
    const { useState, useEffect, useRef } = React;

    // Sample data (~50 visitor records)
    const initialVisitors = [
        { id: 1, name: "John Doe", email: "john@example.com", phone: "1234567890", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-08-12T09:00", checkOut: "2025-08-12T11:00", status: "Checked Out", host: "Alice Smith", badgeId: "V001", preRegistered: false },
        { id: 2, name: "Jane Smith", email: "jane@example.com", phone: "0987654321", purpose: "Interview", company: "HR Solutions", checkIn: "2025-08-12T10:00", checkOut: null, status: "Checked In", host: "Bob Wilson", badgeId: "V002", preRegistered: true },
        { id: 3, name: "Alice Johnson", email: "alice@example.com", phone: "5551234567", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-08-12T08:30", checkOut: "2025-08-12T09:30", status: "Checked Out", host: "Carol Brown", badgeId: "V003", preRegistered: false },
        { id: 4, name: "Bob Wilson", email: "bob@example.com", phone: "4449876543", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-08-12T11:00", checkOut: null, status: "Checked In", host: "David Lee", badgeId: "V004", preRegistered: true },
        { id: 5, name: "Carol Brown", email: "carol@example.com", phone: "3334567890", purpose: "Meeting", company: "Finance Group", checkIn: "2025-08-11T14:00", checkOut: "2025-08-11T16:00", status: "Checked Out", host: "Emma Davis", badgeId: "V005", preRegistered: false },
        { id: 6, name: "David Lee", email: "david@example.com", phone: "2227891234", purpose: "Training", company: "EduTech", checkIn: "2025-08-11T09:00", checkOut: null, status: "Checked In", host: "Frank Miller", badgeId: "V006", preRegistered: true },
        { id: 7, name: "Emma Davis", email: "emma@example.com", phone: "6663214567", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-08-11T13:00", checkOut: "2025-08-11T14:30", status: "Checked Out", host: "Grace Taylor", badgeId: "V007", preRegistered: false },
        { id: 8, name: "Frank Miller", email: "frank@example.com", phone: "7776543210", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-08-10T10:00", checkOut: "2025-08-10T12:00", status: "Checked Out", host: "Henry Adams", badgeId: "V008", preRegistered: false },
        { id: 9, name: "Grace Taylor", email: "grace@example.com", phone: "8881237890", purpose: "Interview", company: "HR Solutions", checkIn: "2025-08-10T15:00", checkOut: null, status: "Checked In", host: "John Doe", badgeId: "V009", preRegistered: true },
        { id: 10, name: "Henry Adams", email: "henry@example.com", phone: "9994561234", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-08-10T08:00", checkOut: "2025-08-10T09:00", status: "Checked Out", host: "Jane Smith", badgeId: "V010", preRegistered: false },
        { id: 11, name: "Isabella Clark", email: "isabella@example.com", phone: "1112223333", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-08-09T09:30", checkOut: "2025-08-09T11:30", status: "Checked Out", host: "John Doe", badgeId: "V011", preRegistered: false },
        { id: 12, name: "James Brown", email: "james@example.com", phone: "2223334444", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-08-09T12:00", checkOut: null, status: "Checked In", host: "Alice Johnson", badgeId: "V012", preRegistered: true },
        { id: 13, name: "Kelly White", email: "kelly@example.com", phone: "3334445555", purpose: "Training", company: "EduTech", checkIn: "2025-08-08T10:00", checkOut: "2025-08-08T12:00", status: "Checked Out", host: "Bob Wilson", badgeId: "V013", preRegistered: false },
        { id: 14, name: "Liam Green", email: "liam@example.com", phone: "4445556666", purpose: "Interview", company: "HR Solutions", checkIn: "2025-08-08T14:00", checkOut: null, status: "Checked In", host: "Carol Brown", badgeId: "V014", preRegistered: true },
        { id: 15, name: "Mia Davis", email: "mia@example.com", phone: "5556667777", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-08-07T08:00", checkOut: "2025-08-07T09:00", status: "Checked Out", host: "David Lee", badgeId: "V015", preRegistered: false },
        { id: 16, name: "Noah Wilson", email: "noah@example.com", phone: "6667778888", purpose: "Meeting", company: "Finance Group", checkIn: "2025-08-07T11:00", checkOut: null, status: "Checked In", host: "Emma Davis", badgeId: "V016", preRegistered: true },
        { id: 17, name: "Olivia Taylor", email: "olivia@example.com", phone: "7778889999", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-08-06T13:00", checkOut: "2025-08-06T14:30", status: "Checked Out", host: "Frank Miller", badgeId: "V017", preRegistered: false },
        { id: 18, name: "Peter Harris", email: "peter@example.com", phone: "8889990000", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-08-06T15:00", checkOut: null, status: "Checked In", host: "Grace Taylor", badgeId: "V018", preRegistered: true },
        { id: 19, name: "Quinn Lee", email: "quinn@example.com", phone: "9990001111", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-08-05T09:00", checkOut: "2025-08-05T11:00", status: "Checked Out", host: "Henry Adams", badgeId: "V019", preRegistered: false },
        { id: 20, name: "Rachel Moore", email: "rachel@example.com", phone: "1112224444", purpose: "Interview", company: "HR Solutions", checkIn: "2025-08-05T12:00", checkOut: null, status: "Checked In", host: "John Doe", badgeId: "V020", preRegistered: true },
        { id: 21, name: "Samuel King", email: "samuel@example.com", phone: "2223335555", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-08-04T08:30", checkOut: "2025-08-04T09:30", status: "Checked Out", host: "Jane Smith", badgeId: "V021", preRegistered: false },
        { id: 22, name: "Tara Scott", email: "tara@example.com", phone: "3334446666", purpose: "Training", company: "EduTech", checkIn: "2025-08-04T10:00", checkOut: null, status: "Checked In", host: "Alice Johnson", badgeId: "V022", preRegistered: true },
        { id: 23, name: "Umar Khan", email: "umar@example.com", phone: "4445557777", purpose: "Meeting", company: "Finance Group", checkIn: "2025-08-03T14:00", checkOut: "2025-08-03T16:00", status: "Checked Out", host: "Bob Wilson", badgeId: "V023", preRegistered: false },
        { id: 24, name: "Victoria Lee", email: "victoria@example.com", phone: "5556668888", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-08-03T13:00", checkOut: null, status: "Checked In", host: "Carol Brown", badgeId: "V024", preRegistered: true },
        { id: 25, name: "William Clark", email: "william@example.com", phone: "6667779999", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-08-02T11:00", checkOut: "2025-08-02T12:30", status: "Checked Out", host: "David Lee", badgeId: "V025", preRegistered: false },
        { id: 26, name: "Xena Young", email: "xena@example.com", phone: "7778880000", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-08-02T09:00", checkOut: null, status: "Checked In", host: "Emma Davis", badgeId: "V026", preRegistered: true },
        { id: 27, name: "Yara Patel", email: "yara@example.com", phone: "8889991111", purpose: "Interview", company: "HR Solutions", checkIn: "2025-08-01T15:00", checkOut: "2025-08-01T16:30", status: "Checked Out", host: "Frank Miller", badgeId: "V027", preRegistered: false },
        { id: 28, name: "Zane Adams", email: "zane@example.com", phone: "9990002222", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-08-01T08:00", checkOut: null, status: "Checked In", host: "Grace Taylor", badgeId: "V028", preRegistered: true },
        { id: 29, name: "Amelia Wright", email: "amelia@example.com", phone: "1113335555", purpose: "Training", company: "EduTech", checkIn: "2025-07-31T10:00", checkOut: "2025-07-31T12:00", status: "Checked Out", host: "Henry Adams", badgeId: "V029", preRegistered: false },
        { id: 30, name: "Benjamin Hill", email: "benjamin@example.com", phone: "2224446666", purpose: "Meeting", company: "Finance Group", checkIn: "2025-07-31T14:00", checkOut: null, status: "Checked In", host: "John Doe", badgeId: "V030", preRegistered: true },
        { id: 31, name: "Chloe Turner", email: "chloe@example.com", phone: "3335557777", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-07-30T13:00", checkOut: "2025-07-30T14:30", status: "Checked Out", host: "Jane Smith", badgeId: "V031", preRegistered: false },
        { id: 32, name: "Daniel Evans", email: "daniel@example.com", phone: "4446668888", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-07-30T11:00", checkOut: null, status: "Checked In", host: "Alice Johnson", badgeId: "V032", preRegistered: true },
        { id: 33, name: "Ella Foster", email: "ella@example.com", phone: "5557779999", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-07-29T09:00", checkOut: "2025-07-29T11:00", status: "Checked Out", host: "Bob Wilson", badgeId: "V033", preRegistered: false },
        { id: 34, name: "Finn Harris", email: "finn@example.com", phone: "6668880000", purpose: "Interview", company: "HR Solutions", checkIn: "2025-07-29T15:00", checkOut: null, status: "Checked In", host: "Carol Brown", badgeId: "V034", preRegistered: true },
        { id: 35, name: "Grace Kim", email: "gracek@example.com", phone: "7779991111", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-07-28T08:00", checkOut: "2025-07-28T09:00", status: "Checked Out", host: "David Lee", badgeId: "V035", preRegistered: false },
        { id: 36, name: "Henry Lopez", email: "henryl@example.com", phone: "8880002222", purpose: "Training", company: "EduTech", checkIn: "2025-07-28T10:00", checkOut: null, status: "Checked In", host: "Emma Davis", badgeId: "V036", preRegistered: true },
        { id: 37, name: "Isla Martin", email: "isla@example.com", phone: "9991113333", purpose: "Meeting", company: "Finance Group", checkIn: "2025-07-27T14:00", checkOut: "2025-07-27T16:00", status: "Checked Out", host: "Frank Miller", badgeId: "V037", preRegistered: false },
        { id: 38, name: "Jack Nguyen", email: "jack@example.com", phone: "1112224444", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-07-27T13:00", checkOut: null, status: "Checked In", host: "Grace Taylor", badgeId: "V038", preRegistered: true },
        { id: 39, name: "Kylie Parker", email: "kylie@example.com", phone: "2223335555", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-07-26T11:00", checkOut: "2025-07-26T12:30", status: "Checked Out", host: "Henry Adams", badgeId: "V039", preRegistered: false },
        { id: 40, name: "Lucas Reed", email: "lucas@example.com", phone: "3334446666", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-07-26T09:00", checkOut: null, status: "Checked In", host: "John Doe", badgeId: "V040", preRegistered: true },
        { id: 41, name: "Maya Singh", email: "maya@example.com", phone: "4445557777", purpose: "Interview", company: "HR Solutions", checkIn: "2025-07-25T15:00", checkOut: "2025-07-25T16:30", status: "Checked Out", host: "Jane Smith", badgeId: "V041", preRegistered: false },
        { id: 42, name: "Nathan Brooks", email: "nathan@example.com", phone: "5556668888", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-07-25T08:00", checkOut: null, status: "Checked In", host: "Alice Johnson", badgeId: "V042", preRegistered: true },
        { id: 43, name: "Olivia Carter", email: "oliviac@example.com", phone: "6667779999", purpose: "Training", company: "EduTech", checkIn: "2025-07-24T10:00", checkOut: "2025-07-24T12:00", status: "Checked Out", host: "Bob Wilson", badgeId: "V043", preRegistered: false },
        { id: 44, name: "Peter Diaz", email: "peterd@example.com", phone: "7778880000", purpose: "Meeting", company: "Finance Group", checkIn: "2025-07-24T14:00", checkOut: null, status: "Checked In", host: "Carol Brown", badgeId: "V044", preRegistered: true },
        { id: 45, name: "Quinn Ellis", email: "quinn@example.com", phone: "8889991111", purpose: "Sales Pitch", company: "Sales Corp", checkIn: "2025-07-23T13:00", checkOut: "2025-07-23T14:30", status: "Checked Out", host: "David Lee", badgeId: "V045", preRegistered: false },
        { id: 46, name: "Riley Ford", email: "riley@example.com", phone: "9990002222", purpose: "Consulting", company: "Consult Inc", checkIn: "2025-07-23T11:00", checkOut: null, status: "Checked In", host: "Emma Davis", badgeId: "V046", preRegistered: true },
        { id: 47, name: "Sophia Gray", email: "sophia@example.com", phone: "1113335555", purpose: "Meeting", company: "Tech Corp", checkIn: "2025-07-22T09:00", checkOut: "2025-07-22T11:00", status: "Checked Out", host: "Frank Miller", badgeId: "V047", preRegistered: false },
        { id: 48, name: "Thomas Hayes", email: "thomas@example.com", phone: "2224446666", purpose: "Interview", company: "HR Solutions", checkIn: "2025-07-22T15:00", checkOut: null, status: "Checked In", host: "Grace Taylor", badgeId: "V048", preRegistered: true },
        { id: 49, name: "Uma Patel", email: "uma@example.com", phone: "3335557777", purpose: "Delivery", company: "Logistics Ltd", checkIn: "2025-07-21T08:00", checkOut: "2025-07-21T09:00", status: "Checked Out", host: "Henry Adams", badgeId: "V049", preRegistered: false },
        { id: 50, name: "Victor Kim", email: "victor@example.com", phone: "4446668888", purpose: "Training", company: "EduTech", checkIn: "2025-07-21T10:00", checkOut: null, status: "Checked In", host: "John Doe", badgeId: "V050", preRegistered: true }
    ];

    // Sample notifications (~50 records)
    const initialNotifications = [
        { id: 1, message: "John Doe checked in for Meeting", time: "2025-08-12T09:00", role: "all" },
        { id: 2, message: "Jane Smith awaiting approval", time: "2025-08-12T10:00", role: "host" },
        { id: 3, message: "Alice Johnson checked out", time: "2025-08-12T09:30", role: "all" },
        { id: 4, message: "Bob Wilson checked in for Consulting", time: "2025-08-12T11:00", role: "all" },
        { id: 5, message: "Carol Brown checked out", time: "2025-08-11T16:00", role: "all" },
        { id: 6, message: "David Lee checked in for Training", time: "2025-08-11T09:00", role: "all" },
        { id: 7, message: "Emma Davis checked out", time: "2025-08-11T14:30", role: "all" },
        { id: 8, message: "Frank Miller checked out", time: "2025-08-10T12:00", role: "all" },
        { id: 9, message: "Grace Taylor awaiting approval", time: "2025-08-10T15:00", role: "host" },
        { id: 10, message: "Henry Adams checked out", time: "2025-08-10T09:00", role: "all" },
        { id: 11, message: "Isabella Clark checked in for Meeting", time: "2025-08-09T09:30", role: "all" },
        { id: 12, message: "James Brown awaiting approval", time: "2025-08-09T12:00", role: "host" },
        { id: 13, message: "Kelly White checked out", time: "2025-08-08T12:00", role: "all" },
        { id: 14, message: "Liam Green checked in for Interview", time: "2025-08-08T14:00", role: "all" },
        { id: 15, message: "Mia Davis checked out", time: "2025-08-07T09:00", role: "all" },
        { id: 16, message: "Noah Wilson checked in for Meeting", time: "2025-08-07T11:00", role: "all" },
        { id: 17, message: "Olivia Taylor checked out", time: "2025-08-06T14:30", role: "all" },
        { id: 18, message: "Peter Harris awaiting approval", time: "2025-08-06T15:00", role: "host" },
        { id: 19, message: "Quinn Lee checked out", time: "2025-08-05T11:00", role: "all" },
        { id: 20, message: "Rachel Moore checked in for Interview", time: "2025-08-05T12:00", role: "all" },
        { id: 21, message: "Samuel King checked out", time: "2025-08-04T09:30", role: "all" },
        { id: 22, message: "Tara Scott checked in for Training", time: "2025-08-04T10:00", role: "all" },
        { id: 23, message: "Umar Khan checked out", time: "2025-08-03T16:00", role: "all" },
        { id: 24, message: "Victoria Lee checked in for Sales Pitch", time: "2025-08-03T13:00", role: "all" },
        { id: 25, message: "William Clark checked out", time: "2025-08-02T12:30", role: "all" },
        { id: 26, message: "Xena Young checked in for Meeting", time: "2025-08-02T09:00", role: "all" },
        { id: 27, message: "Yara Patel checked out", time: "2025-08-01T16:30", role: "all" },
        { id: 28, message: "Zane Adams checked in for Delivery", time: "2025-08-01T08:00", role: "all" },
        { id: 29, message: "Amelia Wright checked out", time: "2025-07-31T12:00", role: "all" },
        { id: 30, message: "Benjamin Hill checked in for Meeting", time: "2025-07-31T14:00", role: "all" },
        { id: 31, message: "Chloe Turner checked out", time: "2025-07-30T14:30", role: "all" },
        { id: 32, message: "Daniel Evans checked in for Consulting", time: "2025-07-30T11:00", role: "all" },
        { id: 33, message: "Ella Foster checked out", time: "2025-07-29T11:00", role: "all" },
        { id: 34, message: "Finn Harris checked in for Interview", time: "2025-07-29T15:00", role: "all" },
        { id: 35, message: "Grace Kim checked out", time: "2025-07-28T09:00", role: "all" },
        { id: 36, message: "Henry Lopez checked in for Training", time: "2025-07-28T10:00", role: "all" },
        { id: 37, message: "Isla Martin checked out", time: "2025-07-27T16:00", role: "all" },
        { id: 38, message: "Jack Nguyen checked in for Sales Pitch", time: "2025-07-27T13:00", role: "all" },
        { id: 39, message: "Kylie Parker checked out", time: "2025-07-26T12:30", role: "all" },
        { id: 40, message: "Lucas Reed checked in for Meeting", time: "2025-07-26T09:00", role: "all" },
        { id: 41, message: "Maya Singh checked out", time: "2025-07-25T16:30", role: "all" },
        { id: 42, message: "Nathan Brooks checked in for Delivery", time: "2025-07-25T08:00", role: "all" },
        { id: 43, message: "Olivia Carter checked out", time: "2025-07-24T12:00", role: "all" },
        { id: 44, message: "Peter Diaz checked in for Meeting", time: "2025-07-24T14:00", role: "all" },
        { id: 45, message: "Quinn Ellis checked out", time: "2025-07-23T14:30", role: "all" },
        { id: 46, message: "Riley Ford checked in for Consulting", time: "2025-07-23T11:00", role: "all" },
        { id: 47, message: "Sophia Gray checked out", time: "2025-07-22T11:00", role: "all" },
        { id: 48, message: "Thomas Hayes checked in for Interview", time: "2025-07-22T15:00", role: "all" },
        { id: 49, message: "Uma Patel checked out", time: "2025-07-21T09:00", role: "all" },
        { id: 50, message: "Victor Kim checked in for Training", time: "2025-07-21T10:00", role: "all" }
    ];

    // LocalStorage utility functions
    const loadFromStorage = (key, defaultValue) => {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : defaultValue;
    };

    const saveToStorage = (key, data) => {
        localStorage.setItem(key, JSON.stringify(data));
    };

    // Dashboard Component
    const Dashboard = ({ visitors, notifications }) => {
        const chartRef = useRef(null);
        const chartInstance = useRef(null);

        useEffect(() => {
            if (chartRef.current) {
                if (chartInstance.current) chartInstance.current.destroy();
                const ctx = chartRef.current.getContext("2d");
                const purposes = visitors.reduce((acc, v) => {
                    acc[v.purpose] = (acc[v.purpose] || 0) + 1;
                    return acc;
                }, {});

                chartInstance.current = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: Object.keys(purposes),
                        datasets: [{
                            label: "Visitor Purpose",
                            data: Object.values(purposes),
                            backgroundColor: ["#3b82f6", "#10b981", "#f59e0b", "#ef4444"],
                            borderColor: ["#1e3a8a", "#047857", "#b45309", "#b91c1c"],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true } },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            return () => chartInstance.current && chartInstance.current.destroy();
        }, [visitors]);

        const todayVisitors = visitors.filter(v => v.checkIn && v.checkIn.startsWith(new Date().toISOString().split("T")[0])).length;
        const activeVisitors = visitors.filter(v => v.status === "Checked In").length;
        const preRegistered = visitors.filter(v => v.preRegistered).length;

        return (
            <div className="space-y-6">
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div className="bg-white p-4 rounded-xl shadow-custom card-hover">
                        <h3 className="text-base font-semibold text-indigo-600">Today's Visitors</h3>
                        <p className="text-2xl font-bold text-gray-800">{todayVisitors}</p>
                    </div>
                    <div className="bg-white p-4 rounded-xl shadow-custom card-hover">
                        <h3 className="text-base font-semibold text-emerald-600">Active Visitors</h3>
                        <p className="text-2xl font-bold text-gray-800">{activeVisitors}</p>
                    </div>
                    <div className="bg-white p-4 rounded-xl shadow-custom card-hover">
                        <h3 className="text-base font-semibold text-amber-600">Pre-Registered</h3>
                        <p className="text-2xl font-bold text-gray-800">{preRegistered}</p>
                    </div>
                </div>
                <div className="bg-white p-4 rounded-xl shadow-custom h-64">
                    <h3 className="text-base font-semibold mb-2 text-indigo-600">Visitor Purpose Distribution</h3>
                    <canvas ref={chartRef}></canvas>
                </div>
            </div>
        );
    };

    // Visitor Form Component
    const VisitorForm = ({ addVisitor, role, preRegister = false }) => {
        const [formData, setFormData] = useState({
            name: "", email: "", phone: "", purpose: "", company: "", host: "", visitDate: ""
        });

        const handleChange = (e) => {
            setFormData({ ...formData, [e.target.name]: e.target.value });
        };

        const handleSubmit = (e) => {
            e.preventDefault();
            const newVisitor = {
                id: Date.now(),
                ...formData,
                checkIn: preRegister ? null : new Date().toISOString(),
                checkOut: null,
                status: role === "gatekeeper" && !preRegister ? "Pending" : preRegister ? "Pre-Registered" : "Checked In",
                badgeId: `V${Math.floor(100 + Math.random() * 900)}`,
                preRegistered
            };
            addVisitor(newVisitor, preRegister ? `Visitor ${formData.name} pre-registered for ${formData.visitDate}` : role === "gatekeeper" ? `New visitor ${formData.name} awaiting approval` : `${formData.name} checked in for ${formData.purpose}`);
            setFormData({ name: "", email: "", phone: "", purpose: "", company: "", host: "", visitDate: "" });
        };

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">{preRegister ? "Pre-Register Visitor" : "Register New Visitor"}</h2>
                <div className="space-y-3">
                    <input type="text" name="name" value={formData.name} onChange={handleChange} placeholder="Name" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    <input type="email" name="email" value={formData.email} onChange={handleChange} placeholder="Email" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    <input type="tel" name="phone" value={formData.phone} onChange={handleChange} placeholder="Phone" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    <input type="text" name="purpose" value={formData.purpose} onChange={handleChange} placeholder="Purpose of Visit" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    <input type="text" name="company" value={formData.company} onChange={handleChange} placeholder="Company" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" />
                    <input type="text" name="host" value={formData.host} onChange={handleChange} placeholder="Host Name" className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    {preRegister && (
                        <input type="date" name="visitDate" value={formData.visitDate} onChange={handleChange} className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required />
                    )}
                    <button onClick={handleSubmit} className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform w-full text-base">Submit</button>
                </div>
            </div>
        );
    };

    // Visitor List Component with Search and Pagination
    const VisitorList = ({ visitors, checkOutVisitor, approveVisitor, role, printCard }) => {
        const [searchTerm, setSearchTerm] = useState("");
        const [currentPage, setCurrentPage] = useState(1);
        const itemsPerPage = 5;

        const filteredVisitors = visitors.filter(v =>
            (role === "host" ? v.status === "Pending" : v.status === "Checked In" || v.status === "Pending") &&
            (v.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                v.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                v.badgeId.toLowerCase().includes(searchTerm.toLowerCase()))
        );

        const totalPages = Math.ceil(filteredVisitors.length / itemsPerPage);
        const paginatedVisitors = filteredVisitors.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Current Visitors</h2>
                <input
                    type="text"
                    placeholder="Search by name, email, or badge ID"
                    value={searchTerm}
                    onChange={(e) => { setSearchTerm(e.target.value); setCurrentPage(1); }}
                    className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base mb-4"
                />
                <div className="overflow-x-auto">
                    <table className="w-full table-auto text-base">
                        <thead>
                        <tr className="bg-indigo-100">
                            <th className="p-2 table-cell">Name</th>
                            <th className="p-2 table-cell hidden sm:table-cell">Email</th>
                            <th className="p-2 table-cell hidden md:table-cell">Phone</th>
                            <th className="p-2 table-cell">Purpose</th>
                            <th className="p-2 table-cell hidden lg:table-cell">Company</th>
                            <th className="p-2 table-cell hidden lg:table-cell">Host</th>
                            <th className="p-2 table-cell hidden md:table-cell">Check-In</th>
                            <th className="p-2 table-cell">Status</th>
                            <th className="p-2 table-cell hidden md:table-cell">Badge</th>
                            <th className="p-2 table-cell">Action</th>
                            <th className="p-2 table-cell hidden sm:table-cell">Print</th>
                        </tr>
                        </thead>
                        <tbody>
                        {paginatedVisitors.map(visitor => (
                            <tr key={visitor.id} className="border-t">
                                <td className="p-2 table-cell truncate">{visitor.name}</td>
                                <td className="p-2 table-cell hidden sm:table-cell truncate">{visitor.email}</td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.phone}</td>
                                <td className="p-2 table-cell truncate">{visitor.purpose}</td>
                                <td className="p-2 table-cell hidden lg:table-cell truncate">{visitor.company}</td>
                                <td className="p-2 table-cell hidden lg:table-cell truncate">{visitor.host}</td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.checkIn ? new Date(visitor.checkIn).toLocaleString() : "N/A"}</td>
                                <td className="p-2 table-cell"><span className="badge text-xs">{visitor.status}</span></td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.badgeId}</td>
                                <td className="p-2 table-cell">
                                    {visitor.status === "Pending" && role === "host" ? (
                                        <button onClick={() => approveVisitor(visitor.id)} className="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 hover:scale-105 transition-transform text-base">Approve</button>
                                    ) : (
                                        <button onClick={() => checkOutVisitor(visitor.id)} className="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 hover:scale-105 transition-transform text-base">Check Out</button>
                                    )}
                                </td>
                                <td className="p-2 table-cell hidden sm:table-cell">
                                    <button onClick={() => printCard(visitor)} className="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 hover:scale-105 transition-transform text-base">Print Card</button>
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
                <div className="flex justify-between mt-4">
                    <button
                        onClick={() => setCurrentPage(p => Math.max(p - 1, 1))}
                        disabled={currentPage === 1}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Previous
                    </button>
                    <span className="text-base">Page {currentPage} of {totalPages}</span>
                    <button
                        onClick={() => setCurrentPage(p => Math.min(p + 1, totalPages))}
                        disabled={currentPage === totalPages}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Next
                    </button>
                </div>
            </div>
        );
    };

    // Visitor History Component with Search and Pagination
    const VisitorHistory = ({ visitors }) => {
        const [searchTerm, setSearchTerm] = useState("");
        const [currentPage, setCurrentPage] = useState(1);
        const itemsPerPage = 5;

        const filteredVisitors = visitors.filter(v =>
            v.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            v.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
            v.badgeId.toLowerCase().includes(searchTerm.toLowerCase())
        );

        const totalPages = Math.ceil(filteredVisitors.length / itemsPerPage);
        const paginatedVisitors = filteredVisitors.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Visitor History</h2>
                <input
                    type="text"
                    placeholder="Search by name, email, or badge ID"
                    value={searchTerm}
                    onChange={(e) => { setSearchTerm(e.target.value); setCurrentPage(1); }}
                    className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base mb-4"
                />
                <div className="overflow-x-auto">
                    <table className="w-full table-auto text-base">
                        <thead>
                        <tr className="bg-indigo-100">
                            <th className="p-2 table-cell">Name</th>
                            <th className="p-2 table-cell hidden sm:table-cell">Email</th>
                            <th className="p-2 table-cell hidden md:table-cell">Phone</th>
                            <th className="p-2 table-cell">Purpose</th>
                            <th className="p-2 table-cell hidden lg:table-cell">Company</th>
                            <th className="p-2 table-cell hidden lg:table-cell">Host</th>
                            <th className="p-2 table-cell hidden md:table-cell">Check-In</th>
                            <th className="p-2 table-cell hidden md:table-cell">Check-Out</th>
                            <th className="p-2 table-cell">Status</th>
                            <th className="p-2 table-cell hidden md:table-cell">Badge</th>
                        </tr>
                        </thead>
                        <tbody>
                        {paginatedVisitors.map(visitor => (
                            <tr key={visitor.id} className="border-t">
                                <td className="p-2 table-cell truncate">{visitor.name}</td>
                                <td className="p-2 table-cell hidden sm:table-cell truncate">{visitor.email}</td>
                                <td className "p-2 table-cell hidden md:table-cell truncate">{visitor.phone}</td>
                                <td className="p-2 table-cell truncate">{visitor.purpose}</td>
                                <td className="p-2 table-cell hidden lg:table-cell truncate">{visitor.company}</td>
                                <td className="p-2 table-cell hidden lg:table-cell truncate">{visitor.host}</td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.checkIn ? new Date(visitor.checkIn).toLocaleString() : "N/A"}</td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.checkOut ? new Date(visitor.checkOut).toLocaleString() : "N/A"}</td>
                                <td className="p-2 table-cell"><span className="badge text-xs">{visitor.status}</span></td>
                                <td className="p-2 table-cell hidden md:table-cell truncate">{visitor.badgeId}</td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                </div>
                <div className="flex justify-between mt-4">
                    <button
                        onClick={() => setCurrentPage(p => Math.max(p - 1, 1))}
                        disabled={currentPage === 1}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Previous
                    </button>
                    <span className="text-base">Page {currentPage} of {totalPages}</span>
                    <button
                        onClick={() => setCurrentPage(p => Math.min(p + 1, totalPages))}
                        disabled={currentPage === totalPages}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Next
                    </button>
                </div>
            </div>
        );
    };

    // Notifications Component
    const Notifications = ({ notifications, role }) => {
        const [currentPage, setCurrentPage] = useState(1);
        const itemsPerPage = 5;

        const filteredNotifications = notifications.filter(n => n.role === "all" || n.role === role);
        const totalPages = Math.ceil(filteredNotifications.length / itemsPerPage);
        const paginatedNotifications = filteredNotifications.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Notifications</h2>
                <div className="space-y-3">
                    {paginatedNotifications.map(notification => (
                        <div key={notification.id} className="p-3 bg-indigo-50 rounded-lg shadow-sm">
                            <p className="text-indigo-800 text-base">{notification.message}</p>
                            <p className="text-xs text-indigo-600">{new Date(notification.time).toLocaleString()}</p>
                        </div>
                    ))}
                </div>
                <div className="flex justify-between mt-4">
                    <button
                        onClick={() => setCurrentPage(p => Math.max(p - 1, 1))}
                        disabled={currentPage === 1}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Previous
                    </button>
                    <span className="text-base">Page {currentPage} of {totalPages}</span>
                    <button
                        onClick={() => setCurrentPage(p => Math.min(p + 1, totalPages))}
                        disabled={currentPage === totalPages}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform disabled:bg-gray-400 text-base"
                    >
                        Next
                    </button>
                </div>
            </div>
        );
    };

    // Reports Component
    const Reports = ({ visitors }) => {
        const [reportType, setReportType] = useState("daily");
        const [reportData, setReportData] = useState({});

        useEffect(() => {
            const generateReport = () => {
                const today = new Date().toISOString().split("T")[0];
                let filteredVisitors = visitors;

                if (reportType === "daily") {
                    filteredVisitors = visitors.filter(v => v.checkIn && v.checkIn.startsWith(today));
                } else if (reportType === "weekly") {
                    const oneWeekAgo = new Date();
                    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
                    filteredVisitors = visitors.filter(v => v.checkIn && new Date(v.checkIn) >= oneWeekAgo);
                } else if (reportType === "monthly") {
                    const oneMonthAgo = new Date();
                    oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
                    filteredVisitors = visitors.filter(v => v.checkIn && new Date(v.checkIn) >= oneMonthAgo);
                }

                const totalVisitors = filteredVisitors.length;
                const checkedIn = filteredVisitors.filter(v => v.status === "Checked In").length;
                const checkedOut = filteredVisitors.filter(v => v.status === "Checked Out").length;
                const pending = filteredVisitors.filter(v => v.status === "Pending").length;
                const preRegistered = filteredVisitors.filter(v => v.status === "Pre-Registered").length;
                const purposes = filteredVisitors.reduce((acc, v) => {
                    acc[v.purpose] = (acc[v.purpose] || 0) + 1;
                    return acc;
                }, {});
                const companies = filteredVisitors.reduce((acc, v) => {
                    acc[v.company] = (acc[v.company] || 0) + 1;
                    return acc;
                }, {});

                setReportData({ totalVisitors, checkedIn, checkedOut, pending, preRegistered, purposes, companies });
            };

            generateReport();
        }, [reportType, visitors]);

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Visitor Reports</h2>
                <select onChange={(e) => setReportType(e.target.value)} className="p-2 border border-indigo-300 rounded-lg mb-4 focus:ring-2 focus:ring-indigo-600 text-base w-full">
                    <option value="daily">Daily Report</option>
                    <option value="weekly">Weekly Report</option>
                    <option value="monthly">Monthly Report</option>
                </select>
                <div className="space-y-3">
                    <p className="text-base"><strong>Total Visitors:</strong> {reportData.totalVisitors || 0}</p>
                    <p className="text-base"><strong>Checked In:</strong> {reportData.checkedIn || 0}</p>
                    <p className="text-base"><strong>Checked Out:</strong> {reportData.checkedOut || 0}</p>
                    <p className="text-base"><strong>Pending Approval:</strong> {reportData.pending || 0}</p>
                    <p className="text-base"><strong>Pre-Registered:</strong> {reportData.preRegistered || 0}</p>
                    <div>
                        <strong className="text-base">Purpose Breakdown:</strong>
                        <ul className="list-disc pl-5 text-base">
                            {Object.entries(reportData.purposes || {}).map(([purpose, count]) => (
                                <li key={purpose} className="text-indigo-800">{purpose}: {count}</li>
                            ))}
                        </ul>
                    </div>
                    <div>
                        <strong className="text-base">Company Breakdown:</strong>
                        <ul className="list-disc pl-5 text-base">
                            {Object.entries(reportData.companies || {}).map(([company, count]) => (
                                <li key={company} className="text-indigo-800">{company}: {count}</li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        );
    };

    // Visitor Card Printing Component
    const VisitorCard = ({ visitor, onClose, isScanPreview = false, onManualConfirm, onAutoAction }) => {
        const cardRef = useRef(null);
        const barcodeRef = useRef(null);

        useEffect(() => {
            if (barcodeRef.current) {
                JsBarcode(barcodeRef.current, visitor.badgeId, {
                    format: "CODE128",
                    displayValue: true,
                    height: 40,
                    width: 2,
                    fontSize: 12
                });
            }
        }, [visitor.badgeId]);

        const handlePrint = () => {
            window.print();
        };

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow w-full max-w-md">
                <div ref={cardRef} className="visitor-card bg-white p-3 flex flex-col justify-between">
                    <div>
                        <h3 className="text-base font-bold text-indigo-600">Visitor Pass</h3>
                        <p className="text-base"><strong>Name:</strong> {visitor.name}</p>
                        <p className="text-base"><strong>Company:</strong> {visitor.company}</p>
                        <p className="text-base"><strong>Purpose:</strong> {visitor.purpose}</p>
                        <p className="text-base"><strong>Host:</strong> {visitor.host}</p>
                        <p className="text-base"><strong>Badge ID:</strong> {visitor.badgeId}</p>
                    </div>
                    <canvas ref={barcodeRef} className="mt-2"></canvas>
                </div>
                <div className="mt-4 flex justify-end space-x-2">
                    {isScanPreview ? (
                        <>
                            <button
                                onClick={() => onManualConfirm(visitor)}
                                className="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 hover:scale-105 transition-transform text-base"
                            >
                                Manual {visitor.status === "Checked In" ? "Check-Out" : "Check-In"}
                            </button>
                            <button
                                onClick={() => onAutoAction(visitor)}
                                className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform text-base"
                            >
                                Auto {visitor.status === "Checked In" ? "Check-Out" : "Check-In"}
                            </button>
                            <button
                                onClick={onClose}
                                className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 hover:scale-105 transition-transform text-base"
                            >
                                Close
                            </button>
                        </>
                    ) : (
                        <>
                            <button onClick={handlePrint} className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform text-base">Print</button>
                            <button onClick={onClose} className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 hover:scale-105 transition-transform text-base">Close</button>
                        </>
                    )}
                </div>
            </div>
        );
    };

    // Scanning Module Component
    const ScanningModule = ({ visitors, checkInVisitor, checkOutVisitor }) => {
        const [badgeId, setBadgeId] = useState("");
        const [scanResult, setScanResult] = useState(null);
        const [scannedVisitor, setScannedVisitor] = useState(null);
        const [confirmManual, setConfirmManual] = useState(false);

        const handleScan = () => {
            const visitor = visitors.find(v => v.badgeId === badgeId);
            if (visitor) {
                if (visitor.status === "Checked Out") {
                    setScanResult("Visitor already checked out");
                    setScannedVisitor(null);
                } else {
                    setScannedVisitor(visitor);
                    setScanResult(null);
                }
            } else {
                setScanResult("Invalid Badge ID");
                setScannedVisitor(null);
            }
            setBadgeId("");
            setConfirmManual(false);
            setTimeout(() => setScanResult(null), 3000);
        };

        const handleAutoAction = (visitor) => {
            if (visitor.status === "Pending" || visitor.status === "Pre-Registered") {
                checkInVisitor(visitor.id);
                setScanResult(`Checked in ${visitor.name}`);
            } else if (visitor.status === "Checked In") {
                checkOutVisitor(visitor.id);
                setScanResult(`Checked out ${visitor.name}`);
            }
            setScannedVisitor(null);
            setTimeout(() => setScanResult(null), 3000);
        };

        const handleManualConfirm = (visitor) => {
            if (!confirmManual) {
                setConfirmManual(true);
                setScanResult(`Confirm ${visitor.status === "Checked In" ? "check-out" : "check-in"} for ${visitor.name}?`);
            } else {
                if (visitor.status === "Pending" || visitor.status === "Pre-Registered") {
                    checkInVisitor(visitor.id);
                    setScanResult(`Checked in ${visitor.name}`);
                } else if (visitor.status === "Checked In") {
                    checkOutVisitor(visitor.id);
                    setScanResult(`Checked out ${visitor.name}`);
                }
                setScannedVisitor(null);
                setConfirmManual(false);
                setTimeout(() => setScanResult(null), 3000);
            }
        };

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Scan Visitor Badge</h2>
                <div className="space-y-3">
                    <input
                        type="text"
                        value={badgeId}
                        onChange={(e) => setBadgeId(e.target.value)}
                        placeholder="Enter Badge ID (e.g., V001)"
                        className="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base"
                    />
                    <button onClick={handleScan} className="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform w-full text-base">Scan</button>
                    {scanResult && <p className="text-emerald-600 text-base">{scanResult}</p>}
                    {scannedVisitor && (
                        <div className="mt-4">
                            <VisitorCard
                                visitor={scannedVisitor}
                                onClose={() => setScannedVisitor(null)}
                                isScanPreview={true}
                                onManualConfirm={handleManualConfirm}
                                onAutoAction={handleAutoAction}
                            />
                        </div>
                    )}
                </div>
            </div>
        );
    };

    // Pre-Registration Module
    const PreRegistration = ({ addVisitor }) => {
        return (
            <VisitorForm addVisitor={addVisitor} role="admin" preRegister={true} />
        );
    };

    // Analytics Module
    const Analytics = ({ visitors }) => {
        const chartRef = useRef(null);
        const chartInstance = useRef(null);

        useEffect(() => {
            if (chartRef.current) {
                if (chartInstance.current) chartInstance.current.destroy();
                const ctx = chartRef.current.getContext("2d");
                const dates = [...new Set(visitors.filter(v => v.checkIn).map(v => v.checkIn.split("T")[0]))].sort();
                const visitorCounts = dates.map(date => ({
                    date,
                    count: visitors.filter(v => v.checkIn && v.checkIn.startsWith(date)).length
                }));

                chartInstance.current = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: visitorCounts.map(v => v.date),
                        datasets: [{
                            label: "Daily Visitors",
                            data: visitorCounts.map(v => v.count),
                            borderColor: "#3b82f6",
                            backgroundColor: "rgba(59, 130, 246, 0.2)",
                            fill: true
                        }]
                    },
                    options: {
                        scales: { y: { beginAtZero: true } },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            return () => chartInstance.current && chartInstance.current.destroy();
        }, [visitors]);

        return (
            <div className="bg-white p-4 rounded-xl shadow-custom hover:shadow-lg transition-shadow">
                <h2 className="text-lg font-semibold mb-4 text-indigo-600">Visitor Analytics</h2>
                <div className="h-64">
                    <canvas ref={chartRef}></canvas>
                </div>
            </div>
        );
    };

    // Main App Component
    const App = () => {
        const [visitors, setVisitors] = useState(loadFromStorage("visitors", initialVisitors));
        const [notifications, setNotifications] = useState(loadFromStorage("notifications", initialNotifications));
        const [activeTab, setActiveTab] = useState("dashboard");
        const [role, setRole] = useState("admin");
        const [selectedVisitor, setSelectedVisitor] = useState(null);
        const [isMenuOpen, setIsMenuOpen] = useState(false);

        useEffect(() => {
            saveToStorage("visitors", visitors);
            saveToStorage("notifications", notifications);
        }, [visitors, notifications]);

        const addVisitor = (visitor, message) => {
            setVisitors([...visitors, visitor]);
            setNotifications([...notifications, { id: Date.now(), message, time: new Date().toISOString(), role: visitor.status === "Pending" || visitor.status === "Pre-Registered" ? "host" : "all" }]);
        };

        const checkOutVisitor = (id) => {
            setVisitors(visitors.map(v =>
                v.id === id ? { ...v, checkOut: new Date().toISOString(), status: "Checked Out" } : v
            ));
            setNotifications([...notifications, { id: Date.now(), message: `Visitor ${visitors.find(v => v.id === id).name} checked out`, time: new Date().toISOString(), role: "all" }]);
        };

        const checkInVisitor = (id) => {
            setVisitors(visitors.map(v =>
                v.id === id ? { ...v, checkIn: new Date().toISOString(), status: "Checked In" } : v
            ));
            setNotifications([...notifications, { id: Date.now(), message: `Visitor ${visitors.find(v => v.id === id).name} checked in`, time: new Date().toISOString(), role: "all" }]);
        };

        const approveVisitor = (id) => {
            setVisitors(visitors.map(v =>
                v.id === id ? { ...v, checkIn: new Date().toISOString(), status: "Checked In" } : v
            ));
            setNotifications([...notifications, { id: Date.now(), message: `Visitor ${visitors.find(v => v.id === id).name} approved`, time: new Date().toISOString(), role: "all" }]);
        };

        const printCard = (visitor) => {
            setSelectedVisitor(visitor);
        };

        const toggleMenu = () => {
            setIsMenuOpen(!isMenuOpen);
        };

        return (
            <div className="min-h-screen gradient-bg p-4">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-xl sm:text-2xl font-bold text-white">Visitor Management System</h1>
                    <div className="flex items-center space-x-2">
                        <select onChange={(e) => setRole(e.target.value)} className="p-2 border border-indigo-300 rounded-lg bg-white text-indigo-600 focus:ring-2 focus:ring-indigo-600 text-base">
                            <option value="admin">Admin</option>
                            <option value="gatekeeper">Gatekeeper</option>
                            <option value="host">Host</option>
                        </select>
                        <button className="hamburger text-white text-2xl sm:hidden" onClick={toggleMenu}>
                            {isMenuOpen ? '✕' : '☰'}
                        </button>
                    </div>
                </div>
                <div className={`nav-menu ${isMenuOpen ? 'open' : ''} flex flex-wrap justify-center space-x-2 sm:space-x-4 sm:flex sm:flex-row mb-4`}>
                    {role === "admin" && (
                        <button
                            onClick={() => { setActiveTab("dashboard"); setIsMenuOpen(false); }}
                            className={`px-4 py-2 rounded-lg m-1 ${activeTab === "dashboard" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                        >
                            Dashboard
                        </button>
                    )}
                    {(role === "admin" || role === "gatekeeper") && (
                        <button
                            onClick={() => { setActiveTab("register"); setIsMenuOpen(false); }}
                            className={`px-4 py-2 rounded-lg m-1 ${activeTab === "register" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                        >
                            Register
                        </button>
                    )}
                    <button
                        onClick={() => { setActiveTab("current"); setIsMenuOpen(false); }}
                        className={`px-4 py-2 rounded-lg m-1 ${activeTab === "current" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                    >
                        Current Visitors
                    </button>
                    {role === "admin" && (
                        <button
                            onClick={() => { setActiveTab("history"); setIsMenuOpen(false); }}
                            className={`px-4 py-2 rounded-lg m-1 ${activeTab === "history" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                        >
                            History
                        </button>
                    )}
                    {role === "admin" && (
                        <button
                            onClick={() => { setActiveTab("reports"); setIsMenuOpen(false); }}
                            className={`px-4 py-2 rounded-lg m-1 ${activeTab === "reports" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                        >
                            Reports
                        </button>
                    )}
                    <button
                        onClick={() => { setActiveTab("notifications"); setIsMenuOpen(false); }}
                        className={`px-4 py-2 rounded-lg m-1 ${activeTab === "notifications" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                    >
                        Notifications
                    </button>
                    {(role === "admin" || role === "gatekeeper") && (
                        <button
                            onClick={() => { setActiveTab("scanning"); setIsMenuOpen(false); }}
                            className={`px-4 py-2 rounded-lg m-1 ${activeTab === "scanning" ? "bg-gradient-to-r from-indigo-600 to-indigo-800 text-white" : "bg-white text-indigo-600"} text-base shadow-custom nav-button`}
                        >
                            Scan Badge
                        </button>
                    )}
                    {role === "admin" && (
                        <button
                            onClick={() => { setActiveTab("prereg