import { Switch, Route, Redirect } from "wouter";
import { queryClient } from "./lib/queryClient";
import { QueryClientProvider } from "@tanstack/react-query";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { ThemeProvider } from "@/components/theme-provider";
import { ThemeToggle } from "@/components/theme-toggle";
import { AppSidebar } from "@/components/app-sidebar";
import { AuthProvider, ProtectedRoute } from "@/lib/auth";
import NotFound from "@/pages/not-found";
import Dashboard from "@/pages/dashboard";
import POS from "@/pages/pos";
import Orders from "@/pages/orders";
import Inventory from "@/pages/inventory";
import Analytics from "@/pages/analytics";
import Employees from "@/pages/employees";
import Settings from "@/pages/settings";
import CustomerOrder from "@/pages/customer-order";
import Login from "@/pages/login";

function Router() {
  return (
    <Switch>
      <Route path="/login" component={Login} />
      <Route path="/order" component={CustomerOrder} />
      <Route path="/">{() => <Redirect to="/login" />}</Route>
      <Route path="/dashboard">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Dashboard />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/pos">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <POS />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/orders">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Orders />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/inventory">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Inventory />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/analytics">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Analytics />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/employees">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Employees />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route path="/settings">
        {() => (
          <ProtectedRoute>
            <AuthenticatedLayout>
              <Settings />
            </AuthenticatedLayout>
          </ProtectedRoute>
        )}
      </Route>
      <Route component={NotFound} />
    </Switch>
  );
}

function AuthenticatedLayout({ children }: { children: React.ReactNode }) {
  const style = {
    "--sidebar-width": "16rem",
    "--sidebar-width-icon": "3rem",
  };

  return (
    <SidebarProvider style={style as React.CSSProperties}>
      <div className="flex h-screen w-full">
        <AppSidebar />
        <div className="flex flex-col flex-1 overflow-hidden">
          <header className="flex items-center justify-between p-4 border-b">
            <SidebarTrigger data-testid="button-sidebar-toggle" />
            <ThemeToggle />
          </header>
          <main className="flex-1 overflow-auto">{children}</main>
        </div>
      </div>
    </SidebarProvider>
  );
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider defaultTheme="light">
        <AuthProvider>
          <TooltipProvider>
            <Router />
            <Toaster />
          </TooltipProvider>
        </AuthProvider>
      </ThemeProvider>
    </QueryClientProvider>
  );
}

export default App;
