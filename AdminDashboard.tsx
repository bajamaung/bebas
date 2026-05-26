import { Link, useNavigate } from "react-router";
import { useEffect } from "react";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import { useAuth } from "@/hooks/useAuth";
import {
  LayoutDashboard,
  Train,
  Calendar,
  DollarSign,
  TrendingUp,
  Ticket,
  ChevronRight,
  ArrowUpRight,
  ArrowDownRight,
  Clock,
  BarChart3,
  AlertCircle,
} from "lucide-react";

export default function AdminDashboard() {
  const { user, isLoading: authLoading } = useAuth();
  const navigate = useNavigate();
  const isAdmin = user?.role === "admin";

  useEffect(() => {
    if (!authLoading && !isAdmin) {
      navigate("/");
    }
  }, [authLoading, isAdmin, navigate]);

  const { data: stats } = trpc.analytics.stats.useQuery(undefined, { enabled: isAdmin });
  const { data: bookingsData } = trpc.booking.allBookings.useQuery(undefined, { enabled: isAdmin });
  const { data: suggestions } = trpc.analytics.suggestions.useQuery(undefined, { enabled: isAdmin });

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(price);
  };

  if (authLoading) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!isAdmin) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="text-center">
          <AlertCircle className="w-16 h-16 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-white mb-2">Access Denied</h2>
          <p className="text-gray-400">You need admin privileges to access this page.</p>
          <Link to="/" className="text-[#ff00ff] mt-4 inline-block">Back to Home</Link>
        </div>
      </div>
    );
  }

  const recentBookings = bookingsData?.slice(0, 10) || [];

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      <div className="max-w-7xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div>
            <h1 className="text-3xl font-bold gradient-text flex items-center gap-3">
              <LayoutDashboard className="w-8 h-8 text-[#ff00ff]" />
              Admin Dashboard
            </h1>
            <p className="text-gray-400 mt-1">Welcome back, {user?.name}</p>
          </div>
          <div className="flex gap-3">
            <Link
              to="/admin/analytics"
              className="flex items-center gap-2 bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white px-4 py-2 rounded-xl font-medium hover:shadow-[0_0_15px_rgba(255,0,255,0.5)] transition-all text-sm"
            >
              <BarChart3 className="w-4 h-4" />
              Analytics
            </Link>
            <Link
              to="/admin/trains"
              className="flex items-center gap-2 border border-[#00ff88] text-[#00ff88] px-4 py-2 rounded-xl font-medium hover:bg-green-500/10 transition-all text-sm"
            >
              <Train className="w-4 h-4" />
              Manage Trains
            </Link>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <div className="glass-card rounded-2xl p-6 neon-border hover-glow">
            <div className="flex items-center justify-between mb-4">
              <div className="w-12 h-12 rounded-xl bg-pink-500/20 flex items-center justify-center">
                <Ticket className="w-6 h-6 text-[#ff00ff]" />
              </div>
              <span className="flex items-center gap-1 text-xs text-[#00ff88]">
                <ArrowUpRight className="w-3 h-3" />
                +12%
              </span>
            </div>
            <p className="text-sm text-gray-400">Total Bookings</p>
            <p className="text-3xl font-bold text-white">{stats?.totalBookings || 0}</p>
          </div>

          <div className="glass-card rounded-2xl p-6 neon-border hover-glow">
            <div className="flex items-center justify-between mb-4">
              <div className="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center">
                <DollarSign className="w-6 h-6 text-[#00ff88]" />
              </div>
              <span className="flex items-center gap-1 text-xs text-[#00ff88]">
                <ArrowUpRight className="w-3 h-3" />
                +8%
              </span>
            </div>
            <p className="text-sm text-gray-400">Total Revenue</p>
            <p className="text-2xl font-bold text-[#00ff88]">{formatPrice(stats?.totalRevenue || 0)}</p>
          </div>

          <div className="glass-card rounded-2xl p-6 neon-border hover-glow">
            <div className="flex items-center justify-between mb-4">
              <div className="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center">
                <Train className="w-6 h-6 text-purple-400" />
              </div>
              <span className="flex items-center gap-1 text-xs text-gray-500">
                <ArrowDownRight className="w-3 h-3" />
                0%
              </span>
            </div>
            <p className="text-sm text-gray-400">Active Trains</p>
            <p className="text-3xl font-bold text-white">{stats?.totalTrains || 0}</p>
          </div>

          <div className="glass-card rounded-2xl p-6 neon-border hover-glow">
            <div className="flex items-center justify-between mb-4">
              <div className="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                <Calendar className="w-6 h-6 text-blue-400" />
              </div>
              <span className="flex items-center gap-1 text-xs text-[#00ff88]">
                <ArrowUpRight className="w-3 h-3" />
                +5%
              </span>
            </div>
            <p className="text-sm text-gray-400">Active Schedules</p>
            <p className="text-3xl font-bold text-white">{stats?.totalSchedules || 0}</p>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Recent Bookings */}
          <div className="lg:col-span-2">
            <div className="glass-card rounded-2xl p-6 neon-border">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-white flex items-center gap-2">
                  <Clock className="w-5 h-5 text-[#ff00ff]" />
                  Recent Bookings
                </h2>
                <Link
                  to="/admin/bookings"
                  className="text-sm text-[#ff00ff] hover:text-[#00ff88] transition-colors flex items-center gap-1"
                >
                  View All <ChevronRight className="w-4 h-4" />
                </Link>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b border-pink-500/20">
                      <th className="text-left py-3 px-2 text-sm text-gray-400 font-medium">ID</th>
                      <th className="text-left py-3 px-2 text-sm text-gray-400 font-medium">Passenger</th>
                      <th className="text-left py-3 px-2 text-sm text-gray-400 font-medium">Train</th>
                      <th className="text-left py-3 px-2 text-sm text-gray-400 font-medium">Class</th>
                      <th className="text-left py-3 px-2 text-sm text-gray-400 font-medium">Status</th>
                      <th className="text-right py-3 px-2 text-sm text-gray-400 font-medium">Price</th>
                    </tr>
                  </thead>
                  <tbody>
                    {recentBookings.map((booking) => (
                      <tr key={booking.id} className="border-b border-gray-800/50 hover:bg-pink-500/5 transition-colors">
                        <td className="py-3 px-2 text-sm text-[#ff00ff] font-mono">
                          #{booking.id?.toString().padStart(4, "0")}
                        </td>
                        <td className="py-3 px-2 text-sm text-white">{booking.passengerName}</td>
                        <td className="py-3 px-2 text-sm text-gray-300">{booking.train?.name}</td>
                        <td className="py-3 px-2">
                          <span className={booking.seatClass === "VIP" ? "badge-vip !py-0.5 !px-2 !text-xs" : "badge-economy !py-0.5 !px-2 !text-xs"}>
                            {booking.seatClass}
                          </span>
                        </td>
                        <td className="py-3 px-2">
                          <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                            booking.bookingStatus === "confirmed" ? "status-active" :
                            booking.bookingStatus === "pending" ? "status-pending" :
                            "bg-red-500/20 text-red-400 border border-red-500/30"
                          }`}>
                            {booking.bookingStatus}
                          </span>
                        </td>
                        <td className="py-3 px-2 text-sm text-[#00ff88] text-right">
                          {formatPrice(parseFloat(booking.ticketPrice))}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {/* AI Suggestions Sidebar */}
          <div className="lg:col-span-1">
            <div className="glass-card rounded-2xl p-6 neon-border">
              <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <TrendingUp className="w-5 h-5 text-[#00ff88]" />
                AI Insights
              </h2>

              <div className="space-y-4">
                {suggestions?.slice(0, 4).map((suggestion) => (
                  <div
                    key={suggestion.id}
                    className="p-4 rounded-xl bg-black/40 border border-pink-500/10 hover:border-pink-500/30 transition-all"
                  >
                    <div className="flex items-center gap-2 mb-2">
                      <span className="text-xs uppercase tracking-wider text-[#ff00ff]">
                        {suggestion.type}
                      </span>
                      <span className="text-xs text-[#00ff88]">{suggestion.confidence}%</span>
                    </div>
                    <h4 className="text-sm font-semibold text-white mb-1">
                      {suggestion.title}
                    </h4>
                    <p className="text-xs text-gray-400 line-clamp-2">
                      {suggestion.content}
                    </p>
                  </div>
                ))}
              </div>
            </div>

            {/* Quick Actions */}
            <div className="glass-card rounded-2xl p-6 neon-border mt-4">
              <h2 className="text-lg font-bold text-white mb-4">Quick Actions</h2>
              <div className="space-y-2">
                <Link
                  to="/admin/trains"
                  className="flex items-center gap-3 p-3 rounded-xl bg-black/40 border border-pink-500/10 hover:border-[#ff00ff] hover:bg-pink-500/5 transition-all"
                >
                  <Train className="w-5 h-5 text-[#ff00ff]" />
                  <span className="text-sm text-white">Manage Trains</span>
                  <ChevronRight className="w-4 h-4 text-gray-500 ml-auto" />
                </Link>
                <Link
                  to="/admin/bookings"
                  className="flex items-center gap-3 p-3 rounded-xl bg-black/40 border border-pink-500/10 hover:border-[#ff00ff] hover:bg-pink-500/5 transition-all"
                >
                  <Ticket className="w-5 h-5 text-[#00ff88]" />
                  <span className="text-sm text-white">View Bookings</span>
                  <ChevronRight className="w-4 h-4 text-gray-500 ml-auto" />
                </Link>
                <Link
                  to="/admin/analytics"
                  className="flex items-center gap-3 p-3 rounded-xl bg-black/40 border border-pink-500/10 hover:border-[#ff00ff] hover:bg-pink-500/5 transition-all"
                >
                  <BarChart3 className="w-5 h-5 text-purple-400" />
                  <span className="text-sm text-white">Analytics</span>
                  <ChevronRight className="w-4 h-4 text-gray-500 ml-auto" />
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
