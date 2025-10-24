import { useState, useEffect } from "react";
import { useLocation } from "wouter";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useAuth } from "@/lib/auth";

export default function Login() {
  const [, setLocation] = useLocation();
  const { isAuthenticated, login } = useAuth();
  const [identifier, setIdentifier] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  // Redirect if already authenticated
  useEffect(() => {
    if (isAuthenticated) {
      setLocation("/dashboard");
    }
  }, [isAuthenticated, setLocation]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (!identifier.trim() || !password) {
      setError("Please enter your username/email and password.");
      return;
    }

    setLoading(true);
    // TODO: Replace with actual authentication API call
    setTimeout(() => {
      setLoading(false);
      if (password === "password") {
        // Successful login - set authentication and redirect to dashboard
        login();
        setLocation("/dashboard");
      } else {
        setError("Invalid credentials.");
      }
    }, 700);
  };

  const handleSocialLogin = (provider: "google" | "facebook") => {
    // TODO: Implement social login
    alert(`Social login with ${provider}`);
  };

  return (
    <div className="flex flex-col min-h-screen bg-gray-100 md:flex-row">
      {/* Left side - Image */}
      <div className="w-full overflow-hidden h-96 md:w-2/3 md:h-screen">
        <img
          src="/bg-login.jpg"
          alt="Cup of coffee on a table"
          className="object-cover w-full h-full"
          style={{ objectPosition: "bottom right" }}
        />
      </div>

      {/* Right side - Login form */}
      <div className="flex items-center justify-center h-screen w-full bg-gray-100 shadow-2xl md:w-1/3">
        <div className="w-full px-8 py-12">
          <div className="flex flex-col items-center mb-4">
            <a
              className="flex flex-col items-center"
              href="https://www.facebook.com/broscafebalayan"
              target="_blank"
              rel="noopener noreferrer"
            >
              <img
                src="/logo.png"
                alt="Bros Cafe Logo"
                className="object-cover w-24 h-24 mb-4 transition-transform rounded-full shadow-sm cursor-pointer md:w-32 md:h-32 hover:scale-105"
              />
            </a>
            <h2 className="flex items-center mb-2 text-xl font-bold text-center md:text-2xl">
              Welcome to
              <span className="ml-2 text-amber-700">BROS CAFE</span>
            </h2>
          </div>

          <form
            onSubmit={handleSubmit}
            className="space-y-4"
            aria-label="login-form"
          >
            {/* Username/Email Input */}
            <div className="space-y-2">
              <Label htmlFor="identifier">Username or Email</Label>
              <Input
                id="identifier"
                name="identifier"
                type="text"
                value={identifier}
                onChange={(e) => setIdentifier(e.target.value)}
                placeholder="Enter your username or email"
                autoComplete="username"
              />
            </div>

            {/* Password Input */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <Label htmlFor="password">Password</Label>
                <a
                  href="#"
                  className="text-sm text-amber-600 hover:text-amber-700"
                >
                  Forgot your password?
                </a>
              </div>
              <div className="relative">
                <Input
                  id="password"
                  name="password"
                  type={showPassword ? "text" : "password"}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter your password"
                  autoComplete="current-password"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute text-gray-500 transform -translate-y-1/2 right-3 top-1/2 hover:text-gray-700"
                  aria-label={showPassword ? "Hide password" : "Show password"}
                >
                  {showPassword ? (
                    <svg
                      className="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                      />
                    </svg>
                  ) : (
                    <svg
                      className="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  )}
                </button>
              </div>
            </div>

            {error && (
              <div
                role="alert"
                className="p-3 text-sm relative text-red-600 bg-red-100 rounded-md"
              >
                {error}
                <button
                  className="flex items-center justify-center transition-all w-8 h-8 rounded-md text-red-600 hover:bg-red-600/10 active:bg-red-600/10 absolute top-1.5 right-1.5"
                  type="button"
                  onClick={() => setError("")}
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    className="h-5 w-5"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12"
                    ></path>
                  </svg>
                </button>
              </div>
            )}

            {/* Sign In Button */}
            <Button
              type="submit"
              disabled={loading}
              className="w-full bg-amber-600 hover:bg-amber-700"
            >
              {loading ? "Signing in..." : "Sign in"}
            </Button>
          </form>

          {/* Divider */}
          <div className="flex items-center my-6">
            <div className="flex-grow border-t border-gray-300" />
            <span className="mx-4 text-sm text-gray-500">or</span>
            <div className="flex-grow border-t border-gray-300" />
          </div>

          {/* Social Login Buttons */}
          <div className="space-y-3">
            <Button
              type="button"
              variant="outline"
              onClick={() => handleSocialLogin("google")}
              className="w-full"
            >
              <svg
                className="w-5 h-5 mr-2"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M21 12.23c0-.63-.06-1.24-.18-1.82H12v3.45h5.45c-.23 1.24-.93 2.29-1.98 3.01v2.5h3.2c1.87-1.72 2.96-4.27 2.96-7.14z"
                  fill="#4285F4"
                />
                <path
                  d="M12 22c2.7 0 4.97-.9 6.62-2.45l-3.2-2.5c-.9.6-2.05.95-3.42.95-2.63 0-4.86-1.77-5.66-4.15H2.98v2.6C4.63 19.9 8.02 22 12 22z"
                  fill="#34A853"
                />
                <path
                  d="M6.34 13.85A6.99 6.99 0 016 12c0-.67.1-1.32.34-1.94V7.46H2.98A10.99 10.99 0 002 12c0 1.77.42 3.44 1.18 4.94l3.16-3.09z"
                  fill="#FBBC05"
                />
                <path
                  d="M12 6.5c1.46 0 2.77.5 3.8 1.48l2.86-2.86C16.95 3.7 14.7 2.8 12 2.8 8.02 2.8 4.63 4.9 2.98 7.46l3.36 2.6C7.14 8.27 9.37 6.5 12 6.5z"
                  fill="#EA4335"
                />
              </svg>
              Continue with Google
            </Button>

            <Button
              type="button"
              variant="outline"
              onClick={() => handleSocialLogin("facebook")}
              className="w-full"
            >
              <svg
                className="w-5 h-5 mr-2"
                viewBox="0 0 24 24"
                fill="#1877F2"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.12 8.44 9.88v-6.99H7.9v-2.9h2.54V9.41c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.45h-1.25c-1.23 0-1.61.76-1.61 1.54v1.85h2.74l-.44 2.9h-2.3v6.99C18.34 21.12 22 16.99 22 12z" />
              </svg>
              Continue with Facebook
            </Button>
          </div>

          {/* Sign Up Link */}
          <div className="flex justify-center mt-6">
            <p className="px-6 py-3 text-sm text-center text-gray-600 bg-gray-200 rounded-lg">
              Don't have an account?{" "}
              <a
                href="#"
                className="text-amber-600 hover:text-amber-700 font-medium"
              >
                Sign up
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
