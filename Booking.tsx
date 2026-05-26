import { useState } from "react";
import { useParams, useNavigate, Link } from "react-router";
import Navbar from "@/components/Navbar";
import { trpc } from "@/providers/trpc";
import { useAuth } from "@/hooks/useAuth";
import {
  Train,
  MapPin,
  Clock,
  Calendar,
  Users,
  CreditCard,
  Shield,
  ChevronRight,
  Check,
  ArrowLeft,
  Armchair,
  AlertCircle,
} from "lucide-react";

export default function Booking() {
  const { scheduleId } = useParams<{ scheduleId: string }>();
  const navigate = useNavigate();
  const { user } = useAuth();
  const [seatClass, setSeatClass] = useState<"VIP" | "Economy">("Economy");
  const [selectedSeat, setSelectedSeat] = useState("");
  const [step, setStep] = useState(1);
  const [passengerName, setPassengerName] = useState(user?.name || "");
  const [passengerEmail, setPassengerEmail] = useState(user?.email || "");
  const [passengerPhone, setPassengerPhone] = useState("");
  const [passengerId, setPassengerId] = useState("");
  const [isProcessing, setIsProcessing] = useState(false);

  const { data: schedule, isLoading } = trpc.train.getSchedule.useQuery(
    { id: Number(scheduleId) },
    { enabled: !!scheduleId }
  );

  const createBooking = trpc.booking.create.useMutation({
    onSuccess: (data) => {
      navigate(`/ticket/${data.bookingId}`);
    },
  });

  const formatPrice = (price: string) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(parseFloat(price));
  };

  const formatDuration = (minutes: number) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${h}h ${m}m`;
  };

  const getTicketPrice = () => {
    if (!schedule?.route) return "0";
    return seatClass === "VIP" ? schedule.route.vipPrice : schedule.route.economyPrice;
  };

  // Generate seat layout
  const generateSeats = () => {
    const seats = [];
    const rows = seatClass === "VIP" ? 5 : 10;
    const cols = ["A", "B", "C", "D"];
    
    for (let row = 1; row <= rows; row++) {
      for (const col of cols) {
        const seatNum = `${seatClass === "VIP" ? "V" : "E"}${row}${col}`;
        const isOccupied = Math.random() < 0.3; // 30% occupied for demo
        seats.push({ number: seatNum, occupied: isOccupied });
      }
    }
    return seats;
  };

  const seats = generateSeats();

  const handleBooking = async () => {
    if (!schedule || !scheduleId) return;
    setIsProcessing(true);

    try {
      await createBooking.mutateAsync({
        userId: user?.id || 1,
        scheduleId: Number(scheduleId),
        trainId: schedule.trainId,
        routeId: schedule.routeId,
        passengerName,
        passengerEmail,
        passengerPhone,
        passengerId,
        seatClass,
        seatNumber: selectedSeat,
        ticketPrice: getTicketPrice(),
      });
    } catch (err) {
      console.error("Booking error:", err);
      setIsProcessing(false);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-black pt-20 flex items-center justify-center">
        <div className="w-16 h-16 border-4 border-[#ff00ff] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!schedule) {
    return (
      <div className="min-h-screen bg-black pt-20">
        <Navbar />
        <div className="text-center py-20">
          <AlertCircle className="w-16 h-16 text-red-500 mx-auto mb-4" />
          <h2 className="text-xl font-bold text-white">Schedule not found</h2>
          <Link to="/schedule" className="text-[#ff00ff] mt-4 inline-block">
            Back to Schedule
          </Link>
        </div>
      </div>
    );
  }

  // Format date safely
  const formatDate = (dateVal: unknown) => {
    if (!dateVal) return "N/A";
    try {
      return new Date(dateVal as string).toLocaleDateString("id-ID");
    } catch {
      return "N/A";
    }
  };

  return (
    <div className="min-h-screen bg-black pt-20">
      <Navbar />

      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Breadcrumb */}
        <div className="flex items-center gap-2 mb-8 text-sm">
          <Link to="/" className="text-gray-500 hover:text-[#ff00ff]">Home</Link>
          <ChevronRight className="w-4 h-4 text-gray-600" />
          <Link to="/schedule" className="text-gray-500 hover:text-[#ff00ff]">Schedule</Link>
          <ChevronRight className="w-4 h-4 text-gray-600" />
          <span className="text-[#ff00ff]">Booking</span>
        </div>

        {/* Step Indicator */}
        <div className="flex items-center justify-center mb-8">
          {[1, 2, 3].map((s) => (
            <div key={s} className="flex items-center">
              <div
                className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm ${
                  s === step
                    ? "bg-[#ff00ff] text-white shadow-[0_0_15px_rgba(255,0,255,0.5)]"
                    : s < step
                    ? "bg-[#00ff88] text-black"
                    : "bg-gray-800 text-gray-500"
                }`}
              >
                {s < step ? <Check className="w-5 h-5" /> : s}
              </div>
              {s < 3 && (
                <div
                  className={`w-20 h-1 mx-2 ${
                    s < step ? "bg-[#00ff88]" : "bg-gray-800"
                  }`}
                />
              )}
            </div>
          ))}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2">
            {step === 1 && (
              <div className="space-y-6">
                {/* Class Selection */}
                <div className="glass-card rounded-2xl p-6 neon-border">
                  <h2 className="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <Train className="w-6 h-6 text-[#ff00ff]" />
                    Select Class
                  </h2>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button
                      onClick={() => { setSeatClass("VIP"); setSelectedSeat(""); }}
                      className={`p-6 rounded-xl border-2 transition-all ${
                        seatClass === "VIP"
                          ? "border-[#ff00ff] bg-pink-500/10 shadow-[0_0_15px_rgba(255,0,255,0.3)]"
                          : "border-gray-700 hover:border-pink-500/50"
                      }`}
                    >
                      <div className="flex items-center justify-between mb-3">
                        <span className="badge-vip">VIP</span>
                        {seatClass === "VIP" && <Check className="w-5 h-5 text-[#ff00ff]" />}
                      </div>
                      <p className="text-2xl font-bold text-white mb-1">
                        {formatPrice(schedule.route?.vipPrice || "0")}
                      </p>
                      <p className="text-sm text-gray-400">{schedule.availableVipSeats} seats available</p>
                    </button>

                    <button
                      onClick={() => { setSeatClass("Economy"); setSelectedSeat(""); }}
                      className={`p-6 rounded-xl border-2 transition-all ${
                        seatClass === "Economy"
                          ? "border-[#00ff88] bg-green-500/10 shadow-[0_0_15px_rgba(0,255,136,0.3)]"
                          : "border-gray-700 hover:border-green-500/50"
                      }`}
                    >
                      <div className="flex items-center justify-between mb-3">
                        <span className="badge-economy">Economy</span>
                        {seatClass === "Economy" && <Check className="w-5 h-5 text-[#00ff88]" />}
                      </div>
                      <p className="text-2xl font-bold text-white mb-1">
                        {formatPrice(schedule.route?.economyPrice || "0")}
                      </p>
                      <p className="text-sm text-gray-400">{schedule.availableEconomySeats} seats available</p>
                    </button>
                  </div>
                </div>

                {/* Seat Selection */}
                <div className="glass-card rounded-2xl p-6 neon-border">
                  <h2 className="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <Armchair className="w-6 h-6 text-[#00ff88]" />
                    Select Seat
                  </h2>
                  
                  {/* Seat Legend */}
                  <div className="flex items-center gap-6 mb-6 text-sm">
                    <div className="flex items-center gap-2">
                      <div className="w-6 h-6 rounded bg-gray-700 border border-gray-600" />
                      <span className="text-gray-400">Available</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-6 h-6 rounded bg-[#ff00ff] border border-[#ff00ff]" />
                      <span className="text-gray-400">Selected</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-6 h-6 rounded bg-gray-900 border border-gray-800 opacity-50" />
                      <span className="text-gray-400">Occupied</span>
                    </div>
                  </div>

                  {/* Seat Grid */}
                  <div className="grid grid-cols-4 gap-3 max-w-md mx-auto">
                    {seats.map((seat) => (
                      <button
                        key={seat.number}
                        onClick={() => !seat.occupied && setSelectedSeat(seat.number)}
                        disabled={seat.occupied}
                        className={`w-14 h-14 rounded-lg font-bold text-sm transition-all ${
                          seat.occupied
                            ? "bg-gray-900/50 border border-gray-800 text-gray-600 cursor-not-allowed"
                            : selectedSeat === seat.number
                            ? "bg-[#ff00ff] border border-[#ff00ff] text-white shadow-[0_0_15px_rgba(255,0,255,0.5)]"
                            : seatClass === "VIP"
                            ? "bg-pink-500/10 border border-pink-500/30 text-pink-400 hover:border-[#ff00ff] hover:shadow-[0_0_10px_rgba(255,0,255,0.3)]"
                            : "bg-green-500/10 border border-green-500/30 text-green-400 hover:border-[#00ff88] hover:shadow-[0_0_10px_rgba(0,255,136,0.3)]"
                        }`}
                      >
                        {seat.number}
                      </button>
                    ))}
                  </div>

                  {selectedSeat && (
                    <p className="text-center mt-4 text-[#00ff88]">
                      Seat <span className="font-bold">{selectedSeat}</span> selected
                    </p>
                  )}
                </div>

                <button
                  onClick={() => setStep(2)}
                  disabled={!selectedSeat}
                  className="w-full bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white py-4 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  Continue
                  <ChevronRight className="w-5 h-5" />
                </button>
              </div>
            )}

            {step === 2 && (
              <div className="space-y-6">
                <div className="glass-card rounded-2xl p-6 neon-border">
                  <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <Users className="w-6 h-6 text-[#ff00ff]" />
                    Passenger Information
                  </h2>

                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm text-gray-400 mb-2">Full Name</label>
                      <input
                        type="text"
                        value={passengerName}
                        onChange={(e) => setPassengerName(e.target.value)}
                        className="w-full px-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
                        placeholder="Enter full name"
                      />
                    </div>
                    <div>
                      <label className="block text-sm text-gray-400 mb-2">Email</label>
                      <input
                        type="email"
                        value={passengerEmail}
                        onChange={(e) => setPassengerEmail(e.target.value)}
                        className="w-full px-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
                        placeholder="Enter email address"
                      />
                    </div>
                    <div>
                      <label className="block text-sm text-gray-400 mb-2">Phone Number</label>
                      <input
                        type="tel"
                        value={passengerPhone}
                        onChange={(e) => setPassengerPhone(e.target.value)}
                        className="w-full px-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
                        placeholder="Enter phone number"
                      />
                    </div>
                    <div>
                      <label className="block text-sm text-gray-400 mb-2">ID Number (KTP/Passport)</label>
                      <input
                        type="text"
                        value={passengerId}
                        onChange={(e) => setPassengerId(e.target.value)}
                        className="w-full px-4 py-3 bg-black/50 border border-pink-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-[#ff00ff] transition-all"
                        placeholder="Enter ID number"
                      />
                    </div>
                  </div>
                </div>

                <div className="flex gap-4">
                  <button
                    onClick={() => setStep(1)}
                    className="flex-1 border border-gray-700 text-white py-4 rounded-xl font-semibold hover:border-[#ff00ff] transition-all flex items-center justify-center gap-2"
                  >
                    <ArrowLeft className="w-5 h-5" />
                    Back
                  </button>
                  <button
                    onClick={() => setStep(3)}
                    disabled={!passengerName || !passengerEmail || !passengerPhone || !passengerId}
                    className="flex-1 bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white py-4 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                  >
                    Continue
                    <ChevronRight className="w-5 h-5" />
                  </button>
                </div>
              </div>
            )}

            {step === 3 && (
              <div className="space-y-6">
                <div className="glass-card rounded-2xl p-6 neon-border">
                  <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <CreditCard className="w-6 h-6 text-[#ff00ff]" />
                    Payment Summary
                  </h2>

                  <div className="space-y-4">
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Train</span>
                      <span className="text-white font-medium">{schedule.train?.name}</span>
                    </div>
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Route</span>
                      <span className="text-white font-medium">
                        {schedule.route?.originStation?.city} → {schedule.route?.destinationStation?.city}
                      </span>
                    </div>
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Date & Time</span>
                      <span className="text-white font-medium">
                        {formatDate(schedule.departureDate)} {schedule.departureTime?.slice(0, 5)}
                      </span>
                    </div>
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Class</span>
                      <span className={seatClass === "VIP" ? "badge-vip" : "badge-economy"}>
                        {seatClass}
                      </span>
                    </div>
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Seat</span>
                      <span className="text-white font-medium">{selectedSeat}</span>
                    </div>
                    <div className="flex justify-between py-3 border-b border-gray-800">
                      <span className="text-gray-400">Passenger</span>
                      <span className="text-white font-medium">{passengerName}</span>
                    </div>
                    <div className="flex justify-between py-4">
                      <span className="text-lg font-bold text-white">Total</span>
                      <span className="text-2xl font-bold text-[#00ff88]">{formatPrice(getTicketPrice())}</span>
                    </div>
                  </div>

                  <div className="mt-6 flex items-center gap-2 text-sm text-gray-400">
                    <Shield className="w-4 h-4 text-[#00ff88]" />
                    Secure payment with SSL encryption
                  </div>
                </div>

                <div className="flex gap-4">
                  <button
                    onClick={() => setStep(2)}
                    className="flex-1 border border-gray-700 text-white py-4 rounded-xl font-semibold hover:border-[#ff00ff] transition-all flex items-center justify-center gap-2"
                  >
                    <ArrowLeft className="w-5 h-5" />
                    Back
                  </button>
                  <button
                    onClick={handleBooking}
                    disabled={isProcessing}
                    className="flex-1 bg-gradient-to-r from-[#ff00ff] to-[#ff44aa] text-white py-4 rounded-xl font-semibold hover:shadow-[0_0_20px_rgba(255,0,255,0.5)] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                  >
                    {isProcessing ? (
                      <>
                        <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                        Processing...
                      </>
                    ) : (
                      <>
                        Pay & Book
                        <ChevronRight className="w-5 h-5" />
                      </>
                    )}
                  </button>
                </div>
              </div>
            )}
          </div>

          {/* Sidebar Summary */}
          <div className="lg:col-span-1">
            <div className="glass-card rounded-2xl p-6 sticky top-24 neon-border">
              <h3 className="text-lg font-bold text-white mb-4">Trip Summary</h3>

              <div className="space-y-4 mb-6">
                <div className="flex items-center gap-3">
                  <Train className="w-5 h-5 text-[#ff00ff]" />
                  <div>
                    <p className="text-sm text-gray-400">Train</p>
                    <p className="text-white font-medium">{schedule.train?.name}</p>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <MapPin className="w-5 h-5 text-[#00ff88]" />
                  <div>
                    <p className="text-sm text-gray-400">Route</p>
                    <p className="text-white font-medium text-sm">
                      {schedule.route?.originStation?.city} → {schedule.route?.destinationStation?.city}
                    </p>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <Clock className="w-5 h-5 text-[#ff00ff]" />
                  <div>
                    <p className="text-sm text-gray-400">Duration</p>
                    <p className="text-white font-medium">{formatDuration(schedule.route?.duration || 0)}</p>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <Calendar className="w-5 h-5 text-[#00ff88]" />
                  <div>
                    <p className="text-sm text-gray-400">Departure</p>
                    <p className="text-white font-medium">
                      {formatDate(schedule.departureDate)} {schedule.departureTime?.slice(0, 5)}
                    </p>
                  </div>
                </div>
              </div>

              <div className="border-t border-pink-500/20 pt-4">
                <div className="flex justify-between items-center mb-2">
                  <span className="text-gray-400">Class</span>
                  <span className={seatClass === "VIP" ? "text-[#ff00ff] font-medium" : "text-[#00ff88] font-medium"}>
                    {seatClass}
                  </span>
                </div>
                {selectedSeat && (
                  <div className="flex justify-between items-center mb-4">
                    <span className="text-gray-400">Seat</span>
                    <span className="text-white font-medium">{selectedSeat}</span>
                  </div>
                )}
                <div className="flex justify-between items-center">
                  <span className="text-gray-400">Price</span>
                  <span className="text-xl font-bold text-[#00ff88]">{formatPrice(getTicketPrice())}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
