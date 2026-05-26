import { useState, useEffect } from "react";
import { Link, useNavigate } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import { useAuth } from "@/hooks/useAuth";
import {
  Ticket,
  ArrowLeft,
  Search,
  Check,
  X,
  Clock,
} from "lucide-react";

export default function AdminBookings() {
  const { user, isLoading: authLoading } = useAuth();
  const navigate = useNavigate();
  const isAdmin = user?.role === "admin";
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("ALL");

  useEffect(() => {
    if (!authLoading && !isAdmin) navigate("/");
  }, [authLoading, isAdmin, navigate]);

  const utils = trpc.useUtils();
  const { data: bookingsData, isLoading } = trpc.booking.allBookings.useQuery(undefined, { enabled: isAdmin });
  
  const updateStatus = trpc.booking.updateStatus.useMutation({
    onSuccess: () => utils.booking.allBookings.invalidate(),
  });
  const cancelBooking = trpc.booking.cancel.useMutation({
    onSuccess: () => utils.booking.allBookings.invalidate(),
  });

  const filteredBookings = bookingsData?.filter((booking) => {
    const matchesSearch = 
      booking.passengerName.toLowerCase().includes(search.toLowerCase()) ||
      booking.id?.toString().includes(search);
    const matchesStatus = statusFilter === "ALL" || booking.bookingStatus === statusFilter.toLowerCase();
    return matchesSearch && matchesStatus;
  }) || [];

  const formatPrice = (price: string) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(parseFloat(price));
  };

  const handleConfirm = (id: number) => {
    updateStatus.mutate({ id, bookingStatus: "confirmed", paymentStatus: "paid" });
  };

  const handleCancel = (id: number) => {
    if (confirm("Are you sure you want to cancel this booking?")) {
      cancelBooking.mutate({ id });
    }
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
                <Ticket className="w-8 h-8 text-[#ff00ff]" />
                Booking Management
              </h1>
              <p className="text-gray-400 mt-1">View and manage all passenger bookings</p>
            </div>
          </div>
        </div>

        {/* Search & Filters */}
        <div className="glass-card rounded-2xl p-4 neon-border mb-6">
          <div className="flex flex-col sm:flex-row gap-4">
            <div className="relative flex-1">
              <Search className="absolute left-3 top-3 w-5 h-5 text-[#ff00ff]" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search by passenger name or booking ID..."
                className="w-full pl-10 pr-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
              />
            </div>
            <div className="flex gap-2 flex-wrap">
              {["ALL", "confirmed", "pending", "cancelled"].map((status) => (
                <button
                  key={status}
                  onClick={() => setStatusFilter(status)}
                  className={`px-4 py-2 rounded-xl text-sm font-medium transition-all capitalize ${
                    statusFilter === status
                      ? status === "confirmed"
                        ? "bg-[#00ff88] text-black"
                        : status === "pending"
                        ? "bg-yellow-500 text-black"
                        : status === "cancelled"
                        ? "bg-red-500 text-white"
                        : "bg-gray-700 text-white"
                      : "bg-black/50 text-gray-400 border border-gray-700 hover:border-gray-500"
                  }`}
                >
                  {status}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Bookings Table */}
        <div className="glass-card rounded-2xl p-6 neon-border">
          {isLoading ? (
            <div className="text-center py-20">
              <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin mx-auto" />
            </div>
          ) : filteredBookings.length === 0 ? (
            <div className="text-center py-20">
              <Ticket className="w-16 h-16 text-gray-600 mx-auto mb-4" />
              <h3 className="text-xl font-bold text-white">No bookings found</h3>
              <p className="text-gray-400">Try adjusting your search criteria</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-pink-500/20">
                    <th className="text-left py-3 px-3 text-sm text-gray-400 font-medium">ID</th>
                    <th className="text-left py-3 px-3 text-sm text-gray-400 font-medium">Passenger</th>
                    <th className="text-left py-3 px-3 text-sm text-gray-400 font-medium">Train</th>
                    <th className="text-left py-3 px-3 text-sm text-gray-400 font-medium">Route</th>
                    <th className="text-center py-3 px-3 text-sm text-gray-400 font-medium">Class</th>
                    <th className="text-center py-3 px-3 text-sm text-gray-400 font-medium">Seat</th>
                    <th className="text-center py-3 px-3 text-sm text-gray-400 font-medium">Status</th>
                    <th className="text-right py-3 px-3 text-sm text-gray-400 font-medium">Price</th>
                    <th className="text-center py-3 px-3 text-sm text-gray-400 font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredBookings.map((booking) => (
                    <tr key={booking.id} className="border-b border-gray-800/50 hover:bg-pink-500/5 transition-colors">
                      <td className="py-3 px-3 text-sm text-[#ff00ff] font-mono">
                        #{booking.id?.toString().padStart(4, "0")}
                      </td>
                      <td className="py-3 px-3">
                        <div>
                          <p className="text-sm text-white font-medium">{booking.passengerName}</p>
                          <p className="text-xs text-gray-500">{booking.passengerEmail}</p>
                        </div>
                      </td>
                      <td className="py-3 px-3 text-sm text-gray-300">{booking.train?.name}</td>
                      <td className="py-3 px-3 text-sm text-gray-400">
                        {booking.schedule ? (
                          <span className="text-xs">
                            {booking.schedule.departureDate ? new Date(booking.schedule.departureDate).toLocaleDateString("id-ID") : "N/A"} | {booking.schedule.departureTime?.slice(0, 5)}
                          </span>
                        ) : "N/A"}
                      </td>
                      <td className="py-3 px-3 text-center">
                        <span className={booking.seatClass === "VIP" ? "badge-vip !py-0.5 !px-2 !text-xs" : "badge-economy !py-0.5 !px-2 !text-xs"}>
                          {booking.seatClass}
                        </span>
                      </td>
                      <td className="py-3 px-3 text-center text-sm text-white font-mono">
                        {booking.seatNumber}
                      </td>
                      <td className="py-3 px-3 text-center">
                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                          booking.bookingStatus === "confirmed" ? "status-active" :
                          booking.bookingStatus === "pending" ? "status-pending" :
                          "bg-red-500/20 text-red-400 border border-red-500/30"
                        }`}>
                          {booking.bookingStatus}
                        </span>
                      </td>
                      <td className="py-3 px-3 text-right text-sm text-[#00ff88] font-semibold">
                        {formatPrice(booking.ticketPrice)}
                      </td>
                      <td className="py-3 px-3">
                        <div className="flex items-center justify-center gap-1">
                          {booking.bookingStatus === "pending" && (
                            <button
                              onClick={() => handleConfirm(booking.id!)}
                              className="p-1.5 rounded-lg bg-green-500/20 text-[#00ff88] hover:bg-green-500/30 transition-all"
                              title="Confirm"
                            >
                              <Check className="w-4 h-4" />
                            </button>
                          )}
                          {booking.bookingStatus !== "cancelled" && (
                            <button
                              onClick={() => handleCancel(booking.id!)}
                              className="p-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-all"
                              title="Cancel"
                            >
                              <X className="w-4 h-4" />
                            </button>
                          )}
                          <Link
                            to={`/ticket/${booking.id}`}
                            className="p-1.5 rounded-lg bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition-all"
                            title="View Ticket"
                          >
                            <Ticket className="w-4 h-4" />
                          </Link>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Summary */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
          <div className="glass-card rounded-2xl p-4 neon-border">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                <Check className="w-5 h-5 text-[#00ff88]" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Confirmed</p>
                <p className="text-xl font-bold text-white">
                  {bookingsData?.filter((b) => b.bookingStatus === "confirmed").length || 0}
                </p>
              </div>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-4 neon-border">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                <Clock className="w-5 h-5 text-yellow-400" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Pending</p>
                <p className="text-xl font-bold text-white">
                  {bookingsData?.filter((b) => b.bookingStatus === "pending").length || 0}
                </p>
              </div>
            </div>
          </div>
          <div className="glass-card rounded-2xl p-4 neon-border">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                <X className="w-5 h-5 text-red-400" />
              </div>
              <div>
                <p className="text-sm text-gray-400">Cancelled</p>
                <p className="text-xl font-bold text-white">
                  {bookingsData?.filter((b) => b.bookingStatus === "cancelled").length || 0}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
