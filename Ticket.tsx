import { useRef } from "react";
import { useParams, Link } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import {
  Train,
  Clock,
  Calendar,
  User,
  CreditCard,
  Tag,
  Shield,
  Check,
  Download,
  Share2,
  Printer,
  ChevronRight,
  QrCode,
  Armchair,
} from "lucide-react";

export default function Ticket() {
  const { bookingId } = useParams<{ bookingId: string }>();
  const ticketRef = useRef<HTMLDivElement>(null);

  const { data: booking, isLoading } = trpc.booking.getById.useQuery(
    { id: Number(bookingId) },
    { enabled: !!bookingId }
  );

  const formatPrice = (price: string) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(parseFloat(price));
  };

  const handlePrint = () => {
    window.print();
  };

  const formatDate = (dateVal: unknown) => {
    if (!dateVal) return "N/A";
    try {
      return new Date(dateVal as string).toLocaleDateString("id-ID");
    } catch {
      return "N/A";
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!booking) {
    return (
      <div className="min-h-screen bg-black pt-20">
        <Navbar />
        <div className="text-center py-20">
          <h2 className="text-xl font-bold text-white">Ticket not found</h2>
          <Link to="/schedule" className="text-[#ff00ff] mt-4 inline-block">
            Back to Schedule
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <div className="flex items-center gap-2 mb-8 text-sm">
          <Link to="/" className="text-gray-500 hover:text-[#ff00ff]">Home</Link>
          <ChevronRight className="w-4 h-4 text-gray-600" />
          <Link to="/schedule" className="text-gray-500 hover:text-[#ff00ff]">Schedule</Link>
          <ChevronRight className="w-4 h-4 text-gray-600" />
          <span className="text-[#00ff88]">E-Ticket</span>
        </div>

        {/* Success Message */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center gap-2 bg-green-500/20 border border-green-500/30 rounded-full px-6 py-3 mb-4">
            <Check className="w-6 h-6 text-[#00ff88]" />
            <span className="text-[#00ff88] font-semibold">Booking Confirmed!</span>
          </div>
          <h1 className="text-3xl font-bold gradient-text mb-2">Your E-Ticket</h1>
          <p className="text-gray-400">Booking Reference: <span className="text-[#ff00ff] font-mono font-bold">KAI-{booking.id?.toString().padStart(6, "0")}</span></p>
        </div>

        {/* Ticket Card */}
        <div
          ref={ticketRef}
          className="glass-card rounded-3xl overflow-hidden neon-border mb-8"
        >
          {/* Ticket Header */}
          <div className="relative bg-gradient-to-r from-[#ff00ff]/20 to-[#00ff88]/20 p-6 border-b border-pink-500/20">
            <div className="absolute inset-0 bg-[url('/hero-train.jpg')] bg-cover bg-center opacity-10" />
            <div className="relative z-10 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Train className="w-10 h-10 text-[#ff00ff]" />
                <div>
                  <h2 className="kai-lightning text-3xl text-white">KAI</h2>
                  <p className="text-xs text-gray-400 uppercase tracking-wider">Kereta Api Indonesia</p>
                </div>
              </div>
              <div className="text-right">
                <span className={booking.seatClass === "VIP" ? "badge-vip" : "badge-economy"}>
                  {booking.seatClass}
                </span>
                <p className="text-sm text-gray-400 mt-2">E-Ticket</p>
              </div>
            </div>
          </div>

          {/* Ticket Body */}
          <div className="p-6 sm:p-8">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Left Column */}
              <div className="space-y-6">
                <div>
                  <p className="text-sm text-gray-500 mb-1">Passenger</p>
                  <div className="flex items-center gap-2">
                    <User className="w-5 h-5 text-[#ff00ff]" />
                    <p className="text-xl font-bold text-white">{booking.passengerName}</p>
                  </div>
                </div>

                <div>
                  <p className="text-sm text-gray-500 mb-1">Train</p>
                  <div className="flex items-center gap-2">
                    <Train className="w-5 h-5 text-[#00ff88]" />
                    <p className="text-lg font-semibold text-white">{booking.train?.name}</p>
                  </div>
                </div>

                <div>
                  <p className="text-sm text-gray-500 mb-1">Route</p>
                  <div className="flex items-center gap-3">
                    <div className="text-center">
                      <p className="text-lg font-bold text-[#ff00ff]">{booking.route?.originStation?.city}</p>
                      <p className="text-xs text-gray-500">{booking.route?.originStation?.name}</p>
                    </div>
                    <div className="flex-1 flex items-center gap-1">
                      <div className="h-0.5 flex-1 bg-gradient-to-r from-[#ff00ff] to-[#00ff88]" />
                      <ChevronRight className="w-4 h-4 text-[#00ff88]" />
                    </div>
                    <div className="text-center">
                      <p className="text-lg font-bold text-[#00ff88]">{booking.route?.destinationStation?.city}</p>
                      <p className="text-xs text-gray-500">{booking.route?.destinationStation?.name}</p>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-500 mb-1">Seat</p>
                    <div className="flex items-center gap-2">
                      <Armchair className="w-4 h-4 text-[#ff00ff]" />
                      <p className="text-lg font-bold text-white">{booking.seatNumber}</p>
                    </div>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 mb-1">Status</p>
                    <div className="flex items-center gap-2">
                      <Shield className="w-4 h-4 text-[#00ff88]" />
                      <p className="text-lg font-bold text-[#00ff88] capitalize">{booking.bookingStatus}</p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Right Column */}
              <div className="space-y-6">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-gray-500 mb-1">Departure Date</p>
                    <div className="flex items-center gap-2">
                      <Calendar className="w-4 h-4 text-[#ff00ff]" />
                      <p className="text-white font-semibold">
                        {formatDate(booking.schedule?.departureDate)}
                      </p>
                    </div>
                  </div>
                  <div>
                    <p className="text-sm text-gray-500 mb-1">Departure Time</p>
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-[#00ff88]" />
                      <p className="text-white font-semibold">{booking.schedule?.departureTime?.slice(0, 5)}</p>
                    </div>
                  </div>
                </div>

                <div>
                  <p className="text-sm text-gray-500 mb-1">Arrival Time</p>
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-[#00ff88]" />
                    <p className="text-white font-semibold">{booking.schedule?.arrivalTime?.slice(0, 5)}</p>
                  </div>
                </div>

                <div>
                  <p className="text-sm text-gray-500 mb-1">Ticket Price</p>
                  <div className="flex items-center gap-2">
                    <Tag className="w-4 h-4 text-[#ff00ff]" />
                    <p className="text-2xl font-bold text-[#00ff88]">{formatPrice(booking.ticketPrice)}</p>
                  </div>
                </div>

                <div>
                  <p className="text-sm text-gray-500 mb-1">Payment Status</p>
                  <div className="flex items-center gap-2">
                    <CreditCard className="w-4 h-4 text-[#00ff88]" />
                    <span className="badge-economy !py-1 !px-3 !text-xs capitalize">
                      {booking.paymentStatus}
                    </span>
                  </div>
                </div>

                {/* QR Code Placeholder */}
                <div className="flex justify-center">
                  <div className="bg-white p-4 rounded-xl">
                    <QrCode className="w-24 h-24 text-black" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Ticket Footer */}
          <div className="bg-gradient-to-r from-pink-500/10 to-green-500/10 p-4 border-t border-pink-500/20">
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-400">
              <div className="flex items-center gap-2">
                <Shield className="w-4 h-4 text-[#00ff88]" />
                <span>This e-ticket is valid for travel. Please arrive 30 minutes before departure.</span>
              </div>
              <p className="text-gray-500">Booked on {formatDate(booking.bookingDate)}</p>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-wrap justify-center gap-4 mb-12">
          <button
            onClick={handlePrint}
            className="flex items-center gap-2 bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white px-6 py-3 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all"
          >
            <Printer className="w-5 h-5" />
            Print Ticket
          </button>
          <button className="flex items-center gap-2 border border-[#00ff88] text-[#00ff88] px-6 py-3 rounded-xl font-semibold hover:bg-green-500/10 transition-all">
            <Download className="w-5 h-5" />
            Download PDF
          </button>
          <button className="flex items-center gap-2 border border-pink-500/30 text-gray-300 px-6 py-3 rounded-xl font-semibold hover:border-[#ff00ff] hover:text-[#ff00ff] transition-all">
            <Share2 className="w-5 h-5" />
            Share
          </button>
        </div>

        {/* Important Notes */}
        <div className="glass-card rounded-2xl p-6">
          <h3 className="text-lg font-bold text-white mb-4">Important Information</h3>
          <ul className="space-y-2 text-sm text-gray-400">
            <li className="flex items-start gap-2">
              <Check className="w-4 h-4 text-[#00ff88] mt-0.5 flex-shrink-0" />
              Please arrive at the station at least 30 minutes before departure time.
            </li>
            <li className="flex items-start gap-2">
              <Check className="w-4 h-4 text-[#00ff88] mt-0.5 flex-shrink-0" />
              Bring a valid ID (KTP/Passport) matching the passenger name on this ticket.
            </li>
            <li className="flex items-start gap-2">
              <Check className="w-4 h-4 text-[#00ff88] mt-0.5 flex-shrink-0" />
              This e-ticket can be shown on your mobile device or printed.
            </li>
            <li className="flex items-start gap-2">
              <Check className="w-4 h-4 text-[#00ff88] mt-0.5 flex-shrink-0" />
              Cancellations must be made at least 24 hours before departure for a refund.
            </li>
          </ul>
        </div>
      </div>
    </div>
  );
}
