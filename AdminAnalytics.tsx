import { useEffect } from "react";
import { Link, useNavigate } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import { useAuth } from "@/hooks/useAuth";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from "chart.js";
import { Line, Pie, Bar, Doughnut } from "react-chartjs-2";
import {
  BarChart3,
  TrendingUp,
  DollarSign,
  Train,
  ArrowLeft,
  PieChart,
  Activity,
  Target,
  Users,
} from "lucide-react";

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

export default function AdminAnalytics() {
  const { user, isLoading: authLoading } = useAuth();
  const navigate = useNavigate();
  const isAdmin = user?.role === "admin";

  useEffect(() => {
    if (!authLoading && !isAdmin) {
      navigate("/");
    }
  }, [authLoading, isAdmin, navigate]);

  const { data: trends } = trpc.analytics.passengerTrends.useQuery(undefined, { enabled: isAdmin });
  const { data: classDist } = trpc.analytics.classDistribution.useQuery(undefined, { enabled: isAdmin });
  const { data: revenueByRoute } = trpc.analytics.revenueByRoute.useQuery(undefined, { enabled: isAdmin });
  const { data: occupancy } = trpc.analytics.occupancyRate.useQuery(undefined, { enabled: isAdmin });
  const { data: stats } = trpc.analytics.stats.useQuery(undefined, { enabled: isAdmin });

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(price);
  };

  // Chart colors
  const pink = "#ff00ff";
  const green = "#00ff88";

  // Line Chart - Passenger Trends
  const lineChartData = {
    labels: trends?.map((t) => t.date) || [],
    datasets: [
      {
        label: "Passengers",
        data: trends?.map((t) => t.passengers) || [],
        borderColor: pink,
        backgroundColor: "rgba(255, 0, 255, 0.2)",
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: pink,
        pointBorderColor: "#fff",
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 8,
      },
    ],
  };

  const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: "Passenger Trends Over Time",
        color: "#fff" as const,
        font: { size: 16, weight: "bold" as const },
      },
    },
    scales: {
      x: {
        grid: { color: "rgba(255, 255, 255, 0.1)" },
        ticks: { color: "#888" },
      },
      y: {
        grid: { color: "rgba(255, 255, 255, 0.1)" },
        ticks: { color: "#888" },
      },
    },
  };

  // Pie Chart - Class Distribution
  const pieChartData = {
    labels: classDist?.map((d) => d.class) || ["VIP", "Economy"],
    datasets: [
      {
        data: classDist?.map((d) => d.count) || [30, 70],
        backgroundColor: [pink, green],
        borderColor: ["rgba(255, 0, 255, 0.5)", "rgba(0, 255, 136, 0.5)"],
        borderWidth: 2,
        hoverOffset: 10,
      },
    ],
  };

  const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom" as const,
        labels: { color: "#fff", padding: 20 },
      },
      title: {
        display: true,
        text: "Ticket Class Distribution",
        color: "#fff" as const,
        font: { size: 16, weight: "bold" as const },
      },
    },
  };

  // Bar Chart - Revenue by Route
  const barChartData = {
    labels: revenueByRoute?.map((r) => r.route) || [],
    datasets: [
      {
        label: "Revenue (IDR)",
        data: revenueByRoute?.map((r) => r.revenue) || [],
        backgroundColor: [
          "rgba(255, 0, 255, 0.8)",
          "rgba(0, 255, 136, 0.8)",
          "rgba(147, 51, 234, 0.8)",
          "rgba(59, 130, 246, 0.8)",
          "rgba(245, 158, 11, 0.8)",
          "rgba(239, 68, 68, 0.8)",
        ],
        borderColor: [
          pink,
          green,
          "#9333ea",
          "#3b82f6",
          "#f59e0b",
          "#ef4444",
        ],
        borderWidth: 2,
        borderRadius: 8,
      },
    ],
  };

  const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      title: {
        display: true,
        text: "Revenue by Route",
        color: "#fff" as const,
        font: { size: 16, weight: "bold" as const },
      },
    },
    scales: {
      x: {
        grid: { color: "rgba(255, 255, 255, 0.1)" },
        ticks: { color: "#888" },
      },
      y: {
        grid: { color: "rgba(255, 255, 255, 0.1)" },
        ticks: {
          color: "#888",
          callback: (value: string | number) => `Rp ${(Number(value) / 1000000).toFixed(0)}M`,
        },
      },
    },
  };

  // Doughnut Chart - Occupancy Rate
  const occupancyData = {
    labels: occupancy?.slice(0, 6).map((o) => o.train.split(" ").slice(0, 2).join(" ")) || [],
    datasets: [
      {
        data: occupancy?.slice(0, 6).map((o) => o.overall) || [],
        backgroundColor: [
          "rgba(255, 0, 255, 0.9)",
          "rgba(0, 255, 136, 0.9)",
          "rgba(147, 51, 234, 0.9)",
          "rgba(59, 130, 246, 0.9)",
          "rgba(245, 158, 11, 0.9)",
          "rgba(239, 68, 68, 0.9)",
        ],
        borderColor: [
          pink,
          green,
          "#9333ea",
          "#3b82f6",
          "#f59e0b",
          "#ef4444",
        ],
        borderWidth: 2,
        hoverOffset: 8,
      },
    ],
  };

  const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom" as const,
        labels: { color: "#fff", padding: 15, font: { size: 11 } },
      },
      title: {
        display: true,
        text: "Train Occupancy Rate (%)",
        color: "#fff" as const,
        font: { size: 16, weight: "bold" as const },
      },
    },
  };

  if (authLoading) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div className="flex items-center gap-4">
            <Link
              to="/admin"
              className="w-10 h-10 rounded-xl border border-pink-500/30 flex items-center justify-center text-[#ff00ff] hover:border-[#ff00ff] hover:shadow-[0_0_10px_rgba(255,0,255,0.3)] transition-all"
            >
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <div>
              <h1 className="text-3xl font-bold gradient-text flex items-center gap-3">
                <BarChart3 className="w-8 h-8 text-[#ff00ff]" />
                Analytics Dashboard
              </h1>
              <p className="text-gray-400 mt-1">Comprehensive booking and revenue insights</p>
            </div>
          </div>
        </div>

        {/* Summary Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-lg bg-pink-500/20 flex items-center justify-center">
                <Users className="w-5 h-5 text-[#ff00ff]" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Total Passengers</p>
                <p className="text-xl font-bold text-white">{stats?.totalBookings || 0}</p>
              </div>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                <DollarSign className="w-5 h-5 text-[#00ff88]" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Total Revenue</p>
                <p className="text-xl font-bold text-[#00ff88]">{formatPrice(stats?.totalRevenue || 0)}</p>
              </div>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                <Train className="w-5 h-5 text-purple-400" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Active Trains</p>
                <p className="text-xl font-bold text-white">{stats?.totalTrains || 0}</p>
              </div>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <Target className="w-5 h-5 text-blue-400" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Active Bookings</p>
                <p className="text-xl font-bold text-white">{stats?.activeBookings || 0}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Charts Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Line Chart */}
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-2 mb-4">
              <TrendingUp className="w-5 h-5 text-[#ff00ff]" />
              <h3 className="text-lg font-bold text-white">Passenger Trends</h3>
            </div>
            <div className="h-80">
              <Line data={lineChartData} options={lineChartOptions} />
            </div>
          </div>

          {/* Pie Chart */}
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-2 mb-4">
              <PieChart className="w-5 h-5 text-[#00ff88]" />
              <h3 className="text-lg font-bold text-white">Class Distribution</h3>
            </div>
            <div className="h-80 flex items-center justify-center">
              <Pie data={pieChartData} options={pieChartOptions} />
            </div>
          </div>

          {/* Bar Chart */}
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-2 mb-4">
              <BarChart3 className="w-5 h-5 text-purple-400" />
              <h3 className="text-lg font-bold text-white">Revenue by Route</h3>
            </div>
            <div className="h-80">
              <Bar data={barChartData} options={barChartOptions} />
            </div>
          </div>

          {/* Doughnut Chart */}
          <div className="glass-card rounded-2xl p-6 neon-border">
            <div className="flex items-center gap-2 mb-4">
              <Activity className="w-5 h-5 text-blue-400" />
              <h3 className="text-lg font-bold text-white">Occupancy Rate</h3>
            </div>
            <div className="h-80 flex items-center justify-center">
              <Doughnut data={occupancyData} options={doughnutOptions} />
            </div>
          </div>
        </div>

        {/* Route Revenue Table */}
        <div className="glass-card rounded-2xl p-6 neon-border mt-8">
          <h3 className="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <DollarSign className="w-5 h-5 text-[#00ff88]" />
            Revenue Breakdown by Route
          </h3>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-pink-500/20">
                  <th className="text-left py-3 px-4 text-sm text-gray-400 font-medium">Route</th>
                  <th className="text-center py-3 px-4 text-sm text-gray-400 font-medium">Bookings</th>
                  <th className="text-right py-3 px-4 text-sm text-gray-400 font-medium">Revenue</th>
                </tr>
              </thead>
              <tbody>
                {revenueByRoute?.map((route, index) => (
                  <tr key={index} className="border-b border-gray-800/50 hover:bg-pink-500/5 transition-colors">
                    <td className="py-3 px-4 text-white font-medium">{route.route}</td>
                    <td className="py-3 px-4 text-center text-gray-300">{route.bookings}</td>
                    <td className="py-3 px-4 text-right text-[#00ff88] font-semibold">{formatPrice(route.revenue)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}
