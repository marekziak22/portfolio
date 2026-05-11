const normalizeBasePath = (value) => {
  if (!value) {
    return "";
  }

  const trimmed = value.trim().replace(/^\/+|\/+$/g, "");
  return trimmed ? `/${trimmed}` : "";
};

const basePath = normalizeBasePath(process.env.NEXT_PUBLIC_BASE_PATH);

cconst nextConfig = {
  output: "export",
  images: {
    unoptimized: true,
  },
  basePath: "/portfolio",
  assetPrefix: "/portfolio/",
};

export default nextConfig;
